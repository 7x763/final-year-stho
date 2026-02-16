<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Ticket;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ProjectTimeline extends Widget
{
    use HasWidgetShield;

    protected ?string $heading = 'Dòng thời gian dự án';

    protected string $view = 'filament.widgets.project-timeline';

    protected int|string|array $columnSpan = 'full';

    public static ?int $sort = 4;

    public string $filter = 'pinned';

    public string $viewMode = 'projects';

    protected $projects = null;

    public function getProjects()
    {
        if ($this->projects !== null) {
            return $this->projects;
        }

        $query = Project::query()
            ->select(['id', 'name', 'start_date', 'end_date', 'pinned_date', 'description'])
            ->with(['tickets' => function ($q): void {
                $q->select(['id', 'project_id', 'name', 'due_date', 'ticket_status_id', 'priority_id'])
                    ->whereNotNull('due_date')
                    ->with(['status:id,name', 'assignees:id,name', 'priority:id,name']);
            }])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($this->filter === 'pinned') {
            $query->whereNotNull('pinned_date')
                ->orderBy('pinned_date', 'desc');
        } else {
            $query->orderBy('start_date')
                ->limit(10); // Limit non-pinned projects to improve performance
        }

        $user = auth()->user();
        $userIsSuperAdmin = $user && (
            (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))
            || (isset($user->role) && $user->role === 'super_admin')
        );

        if (! $userIsSuperAdmin) {
            $query->whereHas('members', function ($query): void {
                $query->where('user_id', auth()->id());
            });
        }

        return $this->projects = $query->get();
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function getTotalProjects()
    {
        $query = Project::query()
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        $userIsSuperAdmin = auth()->user() && (
            (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin'))
            || (isset(auth()->user()->role) && auth()->user()->role === 'super_admin')
        );

        if (! $userIsSuperAdmin) {
            $query->whereHas('members', function ($query): void {
                $query->where('user_id', auth()->id());
            });
        }

        return [
            'all' => $query->count(),
            'pinned' => $query->whereNotNull('pinned_date')->count(),
        ];
    }

    public function getTimelineRange()
    {
        $projects = $this->getProjects();

        if ($projects->isEmpty()) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->addMonths(6)->endOfMonth();

            return [
                'start' => $startDate,
                'end' => $endDate,
                'months' => [],
                'total_days' => $startDate->diffInDays($endDate),
            ];
        }

        $earliestStart = $projects->min('start_date');
        $latestEnd = $projects->max('end_date');

        $startDate = Carbon::parse($earliestStart)->startOfMonth();
        $endDate = Carbon::parse($latestEnd)->endOfMonth();

        // Extend range to show context
        $startDate->subMonth();
        $endDate->addMonth();

        $months = [];
        $current = $startDate->copy();
        $maxMonths = 24; // Safety limit
        $count = 0;

        while ($current->lte($endDate) && $count < $maxMonths) {
            $months[] = [
                'date' => $current->copy(),
                'label' => 'Tháng ' . $current->format('m Y'),
                'short' => 'T' . $current->format('m'),
                'days' => $current->daysInMonth,
            ];
            $current->addMonth();
            $count++;
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'months' => $months,
            'total_days' => $startDate->diffInDays($endDate),
        ];
    }

    protected function getViewData(): array
    {
        $projects = $this->getProjects();
        $today = Carbon::today();
        $counts = $this->getTotalProjects();
        $timelineRange = $this->getTimelineRange();

        $timelineData = [];

        foreach ($projects as $project) {
            if (! $project->start_date || ! $project->end_date) {
                continue;
            }

            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            if ($endDate->lt($startDate)) {
                continue;
            }

            // Calculate position and width for Gantt bar
            $rangeStart = $timelineRange['start'];
            $totalRangeDays = $timelineRange['total_days'];

            $startOffset = $rangeStart->diffInDays($startDate);
            $endOffset = $rangeStart->diffInDays($endDate);

            $leftPercent = ($startOffset / $totalRangeDays) * 100;
            $widthPercent = (($endOffset - $startOffset + 1) / $totalRangeDays) * 100;

            $pastDays = 0;
            $remainingDays = 0;
            $progressPercent = 0;

            if ($today->lt($startDate)) {
                $pastDays = 0;
                $remainingDays = $totalDays;
                $progressPercent = 0;
            } elseif ($today->gt($endDate)) {
                $pastDays = $totalDays;
                $remainingDays = 0;
                $progressPercent = 100;
            } else {
                $pastDays = $startDate->diffInDays($today);
                $remainingDays = $today->diffInDays($endDate);
                $progressPercent = ($pastDays / $totalDays) * 100;
            }

            $status = 'Đang thực hiện';
            $statusColor = 'blue';

            if ($today->gt($endDate)) {
                $status = 'Đã hoàn thành';
                $statusColor = 'green';
            } elseif ($project->remaining_days <= 0) {
                $status = 'Quá hạn';
                $statusColor = 'red';
            } elseif ($project->remaining_days <= 7) {
                $status = 'Sắp đến hạn';
                $statusColor = 'yellow';
            } elseif ($today->lt($startDate)) {
                $status = 'Chưa bắt đầu';
                $statusColor = 'gray';
            }

            // Get ticket statistics
            $ticketStats = [
                'total' => $project->tickets->count(),
                'completed' => $project->tickets->filter(function ($ticket) {
                    return $ticket->status && in_array(strtolower($ticket->status->name), ['done', 'completed', 'closed']);
                })->count(),
                'overdue' => $project->tickets->filter(function ($ticket) {
                    return $ticket->due_date && Carbon::parse($ticket->due_date)->isPast();
                })->count(),
            ];

            $timelineData[] = [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'start_date' => $startDate->format('d/m/Y'),
                'end_date' => $endDate->format('d/m/Y'),
                'start_date_obj' => $startDate,
                'end_date_obj' => $endDate,
                'total_days' => $totalDays,
                'past_days' => $pastDays,
                'remaining_days' => $project->remaining_days,
                'progress_percent' => round($progressPercent, 1),
                'status' => $status,
                'status_color' => $statusColor,
                'left_percent' => max(0, $leftPercent),
                'width_percent' => min(100 - max(0, $leftPercent), $widthPercent),
                'ticket_stats' => $ticketStats,
                'tickets' => $project->tickets->map(function ($ticket) use ($rangeStart, $totalRangeDays) {
                    if (! $ticket->due_date) {
                        return null;
                    }

                    $dueDate = Carbon::parse($ticket->due_date);
                    $dueDayOffset = $rangeStart->diffInDays($dueDate);
                    $duePercent = ($dueDayOffset / $totalRangeDays) * 100;

                    return [
                        'id' => $ticket->id,
                        'name' => $ticket->name,
                        'due_date' => $dueDate->format('d/m/Y'),
                        'due_percent' => max(0, min(100, $duePercent)),
                        'status' => $ticket->status?->name ?? 'Không có trạng thái',
                        'priority' => $ticket->priority?->name ?? 'Không có ưu tiên',
                        'assignees' => $ticket->assignees->pluck('name')->join(', '),
                        'is_overdue' => $dueDate->isPast(),
                    ];
                })->filter()->values(),
            ];
        }

        // Sort by start date
        usort($timelineData, function ($a, $b) {
            return $a['start_date_obj']->timestamp <=> $b['start_date_obj']->timestamp;
        });

        return [
            'projects' => $timelineData,
            'filter' => $this->filter,
            'viewMode' => $this->viewMode,
            'counts' => $counts,
            'timeline_range' => $timelineRange,
            'today_percent' => ($timelineRange['start']->diffInDays(Carbon::today()) / $timelineRange['total_days']) * 100,
        ];
    }
}
