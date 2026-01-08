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

    protected ?string $pollingInterval = '30s';

    protected ?string $heading = 'Overview';

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if ($isSuperAdmin) {
            return $this->getSuperAdminStats();
        } else {
            return $this->getUserStats();
        }
    }

    protected function getSuperAdminStats(): array
    {
        $totalProjects = Project::count();
        $totalTickets = Ticket::count();
        $usersCount = User::count();
        $myTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->where('ticket_users.user_id', auth()->id())
            ->count();

        return [
            Stat::make('Total Projects', $totalProjects)
                ->description('Active projects in the system')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Total Tickets', $totalTickets)
                ->description('Tickets across all projects')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('My Assigned Tickets', $myTickets)
                ->description('Tickets assigned to you')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),

            Stat::make('Team Members', $usersCount)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }

    protected function getUserStats(): array
    {
        $user = auth()->user();
        $userId = $user->id;

        $myProjectIds = DB::table('project_users')
            ->where('user_id', $userId)
            ->pluck('project_id')
            ->toArray();

        $myProjectsCount = count($myProjectIds);

        $projectTicketsCount = Ticket::whereIn('project_id', $myProjectIds)->count();

        $myAssignedTicketsCount = DB::table('ticket_users')
            ->where('user_id', $userId)
            ->count();

        $myCreatedTicketsCount = Ticket::where('created_by', $userId)->count();

        $newTicketsThisWeekCount = Ticket::whereIn('project_id', $myProjectIds)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $myOverdueTicketsCount = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $userId)
            ->where('tickets.due_date', '<', Carbon::now())
            ->whereNotIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->count();

        $myCompletedThisWeekCount = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $userId)
            ->whereIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->where('tickets.updated_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $teamMembersCount = DB::table('project_users')
            ->whereIn('project_id', $myProjectIds)
            ->where('user_id', '!=', $userId)
            ->distinct('user_id')
            ->count();

        return [
            Stat::make('My Projects', $myProjectsCount)
                ->description('Projects you are member of')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('My Assigned Tickets', $myAssignedTicketsCount)
                ->description('Tickets assigned to you')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($myAssignedTicketsCount > 10 ? 'danger' : ($myAssignedTicketsCount > 5 ? 'warning' : 'success')),

            Stat::make('My Created Tickets', $myCreatedTicketsCount)
                ->description('Tickets you created')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make('Project Tickets', $projectTicketsCount)
                ->description('Total tickets in your projects')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Completed This Week', $myCompletedThisWeekCount)
                ->description('Your completed tickets')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($myCompletedThisWeekCount > 0 ? 'success' : 'gray'),

            Stat::make('New Tasks This Week', $newTicketsThisWeekCount)
                ->description('Created in your projects')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('My Overdue Tasks', $myOverdueTicketsCount)
                ->description('Your past due tickets')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($myOverdueTicketsCount > 0 ? 'danger' : 'success'),

            Stat::make('Team Members', $teamMembersCount)
                ->description('People in your projects')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
