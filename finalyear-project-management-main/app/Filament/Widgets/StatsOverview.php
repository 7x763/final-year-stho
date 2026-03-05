<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('Overview');
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $cacheKey = 'stats_overview_' . $user->id;

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $isSuperAdmin = $user->hasRole('super_admin');

            if ($isSuperAdmin) {
                return $this->getSuperAdminStats();
            } else {
                return $this->getUserStats();
            }
        });
    }

    protected function getSuperAdminStats(): array
    {
        $totalProjects = Project::count();
        $totalTickets = Ticket::count();
        $usersCount = User::count();
        $myTickets = auth()->user()->assignedTickets()->count();

        return [
            Stat::make(__('Total Projects'), $totalProjects)
                ->description(__('Active projects in the system'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),
 
            Stat::make(__('Total Tickets'), $totalTickets)
                ->description(__('Tickets across all projects'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),
 
            Stat::make(__('Tickets assigned to me'), $myTickets)
                ->description(__('Number of tickets currently assigned to you'))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
 
            Stat::make(__('Team members'), $usersCount)
                ->description(__('Registered users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }

    protected function getUserStats(): array
    {
        $user = auth()->user();
        $userId = $user->id;

        $myProjectIds = $user->projects()->pluck('projects.id')->toArray();

        $myProjectsCount = count($myProjectIds);

        $projectTicketsCount = Ticket::whereIn('project_id', $myProjectIds)->count();

        $myAssignedTicketsCount = $user->assignedTickets()->count();

        $myCreatedTicketsCount = $user->createdTickets()->count();

        $newTicketsThisWeekCount = Ticket::whereIn('project_id', $myProjectIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $myOverdueTicketsCount = $user->assignedTickets()
            ->where('tickets.due_date', '<', now())
            ->whereHas('status', function ($query) {
                $query->whereNotIn('name', ['Completed', 'Done', 'Closed']);
            })
            ->count();

        $myCompletedThisWeekCount = $user->assignedTickets()
            ->whereHas('status', function ($query) {
                $query->whereIn('name', ['Completed', 'Done', 'Closed']);
            })
            ->where('tickets.updated_at', '>=', now()->subDays(7))
            ->count();

        $teamMembersCount = DB::table('project_members')
            ->whereIn('project_id', $myProjectIds)
            ->where('user_id', '!=', $userId)
            ->distinct('user_id')
            ->count();

        return [
            Stat::make(__('My Projects'), $myProjectsCount)
                ->description(__('Projects you are participating in'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make(__('Tickets assigned to me'), $myAssignedTicketsCount)
                ->description(__('Number of tickets currently assigned to you'))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($myAssignedTicketsCount > 10 ? 'danger' : ($myAssignedTicketsCount > 5 ? 'warning' : 'success')),

            Stat::make(__('Tickets I created'), $myCreatedTicketsCount)
                ->description(__('Number of tickets you created'))
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make(__('Total project tickets'), $projectTicketsCount)
                ->description(__('Number of tickets in your projects'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make(__('Completed this week'), $myCompletedThisWeekCount)
                ->description(__('Number of tickets you completed this week'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($myCompletedThisWeekCount > 0 ? 'success' : 'gray'),

            Stat::make(__('New work this week'), $newTicketsThisWeekCount)
                ->description(__('Number of tickets created this week'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make(__('Overdue work'), $myOverdueTicketsCount)
                ->description(__('Number of tickets past due date'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($myOverdueTicketsCount > 0 ? 'danger' : 'success'),

            Stat::make(__('Team members'), $teamMembersCount)
                ->description(__('Number of people in your projects'))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
