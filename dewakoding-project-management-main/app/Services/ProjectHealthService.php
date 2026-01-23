<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OpenAI\Laravel\Facades\OpenAI;

class ProjectHealthService
{
    /**
     * Analyze project health and return a summary of findings.
     */
    public function analyze(int $projectId): array
    {
        $project = Project::with(['tickets', 'ticketStatuses'])->findOrFail($projectId);
        
        $bottlenecks = $this->getBottlenecks($projectId);
        $forecast = $this->getForecast($project);
        
        $analysis = [
            'project_name' => $project->name,
            'overall_status' => $this->calculateOverallStatus($forecast),
            'forecast' => $forecast,
            'bottlenecks' => $bottlenecks,
            'recommendations' => $this->generateRecommendations($bottlenecks, $forecast),
        ];

        $analysis['ai_summary'] = $this->getAiAnalysis($analysis);

        return $analysis;
    }

    /**
     * Get AI analysis from OpenAI.
     */
    private function getAiAnalysis(array $data): string
    {
        if (!config('openai.api_key') || str_contains(config('openai.api_key'), 'your-openai-api-key')) {
            return "Chưa cấu hình OpenAI API Key để nhận phân tích chuyên sâu.";
        }

        try {
            $prompt = "Bạn là một chuyên gia quản lý dự án Agile. Dưới đây là dữ liệu về sức khỏe của dự án '{$data['project_name']}':\n";
            $prompt .= "- Trạng thái tổng thể: {$data['overall_status']}\n";
            $prompt .= "- Tiến độ: {$data['forecast']['progress']}%\n";
            $prompt .= "- Vận tốc hoàn thành: {$data['forecast']['velocity']} ticket/ngày\n";
            $prompt .= "- Dự kiến hoàn thành: {$data['forecast']['estimated_completion_date']}\n";
            $prompt .= "- Thông điệp: {$data['forecast']['message']}\n";
            $prompt .= "- Các nút thắt cổ chai:\n";
            foreach ($data['bottlenecks'] as $b) {
                if ($b['is_bottleneck']) {
                    $prompt .= "  + {$b['status_name']}: trung bình {$b['avg_duration_hours']} giờ (Cảnh báo nghẽn)\n";
                }
            }
            $prompt .= "\nHãy đưa ra một đoạn nhận xét ngắn gọn (khoảng 3-4 câu) bằng tiếng Việt về tình hình dự án và lời khuyên chiến lược.";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return "Không thể kết nối với AI: " . $e->getMessage();
        }
    }


    /**
     * Identify bottlenecks based on TicketHistory.
     */
    private function getBottlenecks(int $projectId): Collection
    {
        $tickets = Ticket::where('project_id', $projectId)->get();
        $ticketIds = $tickets->pluck('id');
        
        $histories = TicketHistory::whereIn('ticket_id', $ticketIds)
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

    private function generateRecommendations(Collection $bottlenecks, array $forecast): array
    {
        $recommendations = [];
        
        $majorBottlenecks = $bottlenecks->where('is_bottleneck', true);
        foreach ($majorBottlenecks as $b) {
            $recommendations[] = "Xem xét lại quy trình tại trạng thái '{$b['status_name']}', thời gian xử lý trung bình đang cao ({$b['avg_duration_hours']} giờ).";
        }
        
        if ($forecast['status'] === 'delayed') {
            $recommendations[] = "Cần tăng tốc độ hoàn thành ticket (hiện tại: {$forecast['velocity']} ticket/ngày) hoặc lùi thời hạn dự án.";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Tiếp tục duy trì tiến độ hiện tại.";
        }
        
        return $recommendations;
    }
}
