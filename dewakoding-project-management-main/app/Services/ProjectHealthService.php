<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        
        return [
            'project_name' => $project->name,
            'overall_status' => $this->calculateOverallStatus($forecast),
            'forecast' => $forecast,
            'bottlenecks' => $bottlenecks,
            'recommendations' => $this->generateRecommendations($bottlenecks, $forecast),
        ];
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
