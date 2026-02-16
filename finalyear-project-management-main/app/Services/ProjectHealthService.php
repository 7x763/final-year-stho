<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectHealthService
{
    /**
     * Analyze project health and return a summary of findings.
     * 
     * @param bool $forceRefresh If true, dispatches a new job even if data exists.
     */
    public function analyze(int $projectId, bool $forceRefresh = false): array
    {
        $project = Project::with(['tickets', 'ticketStatuses'])->findOrFail($projectId);
        
        // If data exists and valid (e.g. < 4 hours old) and not forced, return it
        if (!$forceRefresh && $project->ai_analysis_status === 'completed' && $project->ai_analysis_at) {
             // We still need fresh stats for the UI, but we use cached AI summary
            $stats = $this->calculateStats($projectId);
            $stats['ai_summary'] = $project->ai_analysis;
            $stats['analysis_status'] = 'completed';
            $stats['analysis_at'] = $project->ai_analysis_at;
            return $stats;
        }

        // If status is idle or failed, or forced, we dispatch job
        if ($forceRefresh || in_array($project->ai_analysis_status, ['idle', 'failed'])) {
            $this->dispatchAnalysis($projectId);
        }

        // Return current stats with status indicator
        $stats = $this->calculateStats($projectId);
        $stats['ai_summary'] = match($project->ai_analysis_status) {
            'processing' => 'Đang phân tích dữ liệu... Vui lòng đợi trong giây lát.',
            'failed' => 'Phân tích thất bại: ' . $project->ai_analysis,
            default => 'Chưa có dữ liệu phân tích. Đang khởi chạy...',
        };
        $stats['analysis_status'] = $project->ai_analysis_status;
        
        return $stats;
    }

    public function dispatchAnalysis(int $projectId): void
    {
        \App\Models\Project::where('id', $projectId)->update(['ai_analysis_status' => 'processing']);
        // Use dispatchSync to ensure it runs immediately without needing a separate queue worker
        \App\Jobs\AnalyzeProjectHealthJob::dispatchSync($projectId);
    }

    public function calculateStats(int $projectId): array
    {
        $project = Project::with(['tickets.assignees', 'tickets.status', 'tickets.priority', 'ticketStatuses'])->findOrFail($projectId);
        
        // 1. Enrich: Overdue Tickets
        $overdueTickets = $project->tickets->filter(function ($ticket) {
            return $ticket->due_date && $ticket->due_date < now() && !$ticket->status?->is_completed;
        });

        // 2. Enrich: Stale Tickets (No updates for 7 days)
        $staleTickets = $project->tickets->filter(function ($ticket) {
            return !$ticket->status?->is_completed && $ticket->updated_at < now()->subDays(7);
        });

        // 3. Enrich: Priority Breakdown
        $priorityBreakdown = $project->tickets->filter(fn($t) => !$t->status?->is_completed)
            ->groupBy(fn($t) => $t->priority?->name ?? 'Không xác định')
            ->map(fn($group) => $group->count());

        // 4. Enrich: Member Workload
        $memberWorkload = [];
        foreach ($project->tickets as $ticket) {
            if (!$ticket->status?->is_completed) {
                foreach ($ticket->assignees as $assignee) {
                    if (!isset($memberWorkload[$assignee->name])) {
                        $memberWorkload[$assignee->name] = 0;
                    }
                    $memberWorkload[$assignee->name]++;
                }
            }
        }
        arsort($memberWorkload); // Sort by busiest
        $topWorkload = array_slice($memberWorkload, 0, 3); // Top 3 busiest

        $bottlenecks = $this->getBottlenecks($projectId);
        $forecast = $this->getForecast($project);
        
        return [
            'project_name' => $project->name,
            'overall_status' => $this->calculateOverallStatus($forecast),
            'forecast' => $forecast,
            'bottlenecks' => $bottlenecks,
            'overdue_count' => $overdueTickets->count(),
            'stale_count' => $staleTickets->count(),
            'priority_breakdown' => $priorityBreakdown,
            'top_workload' => $topWorkload,
            'recommendations' => $this->generateRecommendations($bottlenecks, $forecast, $staleTickets->count()),
        ];
    }

    /**
     * Get AI analysis from Gemini.
     */
    public function getAiAnalysis(array $data): string
    {
        try {
            $apiKey = trim(config('services.gemini.api_key'));
            if (!$apiKey) {
                return "Chưa cấu hình Gemini API Key.";
            }

            $prompt = "Bạn là một quản lý dự án Agile cao cấp (Senior Agile PM). Hãy phân tích sức khỏe dự án '{$data['project_name']}' dựa trên dữ liệu sau:\n\n";
            $prompt .= "1. TỔNG QUAN:\n";
            $prompt .= "- Trạng thái: {$data['overall_status']}\n";
            $prompt .= "- Tiến độ: {$data['forecast']['progress']}%\n";
            $prompt .= "- Vận tốc: {$data['forecast']['velocity']} ticket/ngày\n";
            $prompt .= "- Dự kiến xong: {$data['forecast']['estimated_completion_date']}\n\n";

            $prompt .= "2. VẤN ĐỀ CẦN CHÚ Ý:\n";
            $prompt .= "- Vé quá hạn (Overdue): {$data['overdue_count']}\n";
            $prompt .= "- Vé bị lãng quên (Stale > 7 ngày): {$data['stale_count']}\n";
            
            if ($data['priority_breakdown']->isNotEmpty()) {
                $prompt .= "- Phân bổ độ ưu tiên (Ticket chưa xong): " . $data['priority_breakdown']->map(fn($v, $k) => "$k: $v")->join(', ') . "\n";
            }

            if (!empty($data['top_workload'])) {
                $prompt .= "- Top thành viên đang ôm việc: ";
                foreach ($data['top_workload'] as $name => $count) {
                    $prompt .= "$name ($count), ";
                }
                $prompt .= "\n";
            }

            $prompt .= "\n3. NÚT THẮT CỔ CHAI (BOTTLENECKS):\n";
            foreach ($data['bottlenecks'] as $b) {
                if ($b['is_bottleneck']) {
                    $prompt .= "- [NGHIÊM TRỌNG] Trạng thái '{$b['status_name']}': trung bình mất {$b['avg_duration_hours']} giờ.\n";
                }
            }

            $prompt .= "\nYÊU CẦU ĐẦU RA:\n";
            $prompt .= "Hãy viết một báo cáo ngắn gọn (khoảng 150-200 từ) bằng tiếng Việt tự nhiên, chuyên nghiệp. Chia làm 3 phần:\n";
            $prompt .= "- **Đánh giá chung**: Nhận xét tình hình.\n";
            $prompt .= "- **Rủi ro chính**: Chỉ ra điểm yếu nhất (người quá tải, quy trình chậm, hay ticket bị bỏ quên).\n";
            $prompt .= "- **Hành động đề xuất**: 3 việc cụ thể cần làm ngay.";

            // Use gemini-2.5-flash
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Gemini Project Health Error: ' . $response->body());
                return "Không thể phân tích: Lỗi kết nối Gemini API.";
            }

            $responseData = $response->json();
            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Không nhận được phân tích từ AI.';
            
        } catch (\Exception $e) {
            return "Lỗi hệ thống: " . $e->getMessage();
        }
    }

    /**
     * Identify bottlenecks based on TicketHistory (Last 30 Days).
     */
    private function getBottlenecks(int $projectId): Collection
    {
        $tickets = Ticket::where('project_id', $projectId)->get();
        $ticketIds = $tickets->pluck('id');
        
        $histories = TicketHistory::whereIn('ticket_id', $ticketIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('status')
            ->orderBy('ticket_id')
            ->orderBy('created_at')
            ->get();

        $durations = []; // [status_id => [duration_in_seconds, ...]]

        foreach ($histories->groupBy('ticket_id') as $ticketId => $ticketHistories) {
            for ($i = 0; $i < count($ticketHistories) - 1; $i++) {
                $current = $ticketHistories[$i];
                $next = $ticketHistories[$i + 1];
                
                $duration = $current->created_at->diffInSeconds($next->created_at);
                $statusId = $current->ticket_status_id;
                
                if (!isset($durations[$statusId])) {
                    $durations[$statusId] = [];
                }
                $durations[$statusId][] = $duration;
            }
            
            // Add current status duration
            $last = $ticketHistories->last();
            $duration = $last->created_at->diffInSeconds(now());
            $statusId = $last->ticket_status_id;
            
            if (!isset($durations[$statusId])) {
                $durations[$statusId] = [];
            }
            $durations[$statusId][] = $duration;
        }

        $projectStatuses = TicketStatus::where('project_id', $projectId)->get();
        
        return $projectStatuses->map(function ($status) use ($durations) {
            $statusDurations = $durations[$status->id] ?? [];
            $avgDuration = count($statusDurations) > 0 ? array_sum($statusDurations) / count($statusDurations) : 0;
            
            return [
                'status_id' => $status->id,
                'status_name' => $status->name,
                'avg_duration_hours' => round($avgDuration / 3600, 2),
                'is_bottleneck' => $this->isBottleneck($status, $avgDuration, $durations),
            ];
        })->filter(fn($item) => $item['avg_duration_hours'] > 0);
    }

    private function isBottleneck($status, $avgDuration, $allDurations): bool
    {
        if ($status->is_completed) return false;
        
        $allAvgs = collect($allDurations)->map(fn($durs) => array_sum($durs) / count($durs));
        $globalAvg = $allAvgs->avg();
        
        if ($globalAvg == 0) return false;

        // A status is a bottleneck if it's 50% slower than average
        return $avgDuration > ($globalAvg * 1.5);
    }

    /**
     * Forecast project completion.
     */
    private function getForecast(Project $project): array
    {
        $totalTickets = $project->tickets()->count();
        $completedTickets = $project->completedTickets()->count();
        $remainingTickets = $totalTickets - $completedTickets;
        
        if ($totalTickets === 0) {
            return ['status' => 'unknown', 'message' => 'Chưa có ticket nào để phân tích.'];
        }

        // Calculate velocity (completed tickets per day since project start or first ticket completion)
        $firstCompletion = TicketHistory::whereIn('ticket_id', $project->tickets()->pluck('id'))
            ->whereHas('status', fn($q) => $query = $q->where('is_completed', true))
            ->orderBy('created_at')
            ->first();
            
        $startDate = $firstCompletion ? $firstCompletion->created_at : ($project->start_date ?: $project->created_at);
        $daysPassed = max(1, $startDate->diffInDays(now()));
        $velocity = $completedTickets / $daysPassed;

        if ($velocity == 0) {
            return [
                'status' => 'delayed',
                'progress' => 0,
                'estimated_completion_date' => null,
                'message' => 'Chưa có ticket nào hoàn thành. Dự án đang bị đình trệ.',
                'velocity' => 0, // ADDED: Ensure velocity key exists
            ];
        }

        $daysToComplete = ceil($remainingTickets / $velocity);
        $estimatedCompletionDate = now()->addDays($daysToComplete);
        
        $isOnTrack = true;
        if ($project->end_date && $estimatedCompletionDate->gt($project->end_date)) {
            $isOnTrack = false;
        }

        return [
            'status' => $isOnTrack ? 'on_track' : 'delayed',
            'progress' => round(($completedTickets / $totalTickets) * 100, 1),
            'estimated_completion_date' => $estimatedCompletionDate->toDateString(),
            'days_remaining_needed' => $daysToComplete,
            'velocity' => round($velocity, 2), // tickets per day
            'message' => $isOnTrack ? 'Dự án đang đúng tiến độ.' : 'Dự án có nguy cơ chậm tiến độ so với ngày kết thúc dự kiến.',
        ];
    }

    private function calculateOverallStatus(array $forecast): string
    {
        return $forecast['status'] === 'on_track' ? 'Good' : ($forecast['status'] === 'delayed' ? 'At Risk' : 'Unknown');
    }

    private function generateRecommendations(Collection $bottlenecks, array $forecast, int $staleCount): array
    {
        $recommendations = [];
        
        $majorBottlenecks = $bottlenecks->where('is_bottleneck', true);
        foreach ($majorBottlenecks as $b) {
            $recommendations[] = "Xem xét lại quy trình tại trạng thái '{$b['status_name']}', thời gian xử lý trung bình đang cao ({$b['avg_duration_hours']} giờ).";
        }
        
        if ($forecast['status'] === 'delayed') {
            $velocity = $forecast['velocity'] ?? 0; // ADDED: Fallback for safety
            $recommendations[] = "Cần tăng tốc độ hoàn thành ticket (hiện tại: {$velocity} ticket/ngày) hoặc lùi thời hạn dự án.";
        }
        
        if ($staleCount > 0) {
            $recommendations[] = "Có {$staleCount} thẻ việc không được cập nhật trong 7 ngày qua. Hãy rà soát lại.";
        }

        if (empty($recommendations)) {
            $recommendations[] = "Tiếp tục duy trì tiến độ hiện tại.";
        }
        
        return $recommendations;
    }
}
