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

    protected ?string $heading = 'Tổng quan';

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
            Stat::make('Tổng số dự án', $totalProjects)
                ->description('Dự án đang hoạt động trong hệ thống')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Tổng số vé hỗ trợ', $totalTickets)
                ->description('Vé hỗ trợ trên tất cả dự án')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Vé hỗ trợ được giao cho tôi', $myTickets)
                ->description('Số lượng vé đang được giao cho bạn')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),

            Stat::make('Thành viên nhóm', $usersCount)
                ->description('Người dùng đã đăng ký')
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
            Stat::make('Dự án của tôi', $myProjectsCount)
                ->description('Dự án bạn đang tham gia')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Vé hỗ trợ được giao cho tôi', $myAssignedTicketsCount)
                ->description('Số lượng vé đang được giao cho bạn')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($myAssignedTicketsCount > 10 ? 'danger' : ($myAssignedTicketsCount > 5 ? 'warning' : 'success')),

            Stat::make('Vé hỗ trợ tôi đã tạo', $myCreatedTicketsCount)
                ->description('Số lượng vé hỗ trợ bạn đã tạo')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make('Tổng số vé trong dự án', $projectTicketsCount)
                ->description('Số lượng vé hỗ trợ trong các dự án của bạn')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Đã hoàn thành trong tuần', $myCompletedThisWeekCount)
                ->description('Số lượng vé bạn đã hoàn thành tuần này')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($myCompletedThisWeekCount > 0 ? 'success' : 'gray'),

            Stat::make('Công việc mới trong tuần', $newTicketsThisWeekCount)
                ->description('Số lượng vé mới được tạo trong tuần này')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('Công việc quá hạn', $myOverdueTicketsCount)
                ->description('Số lượng vé đã quá hạn chót')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($myOverdueTicketsCount > 0 ? 'danger' : 'success'),

            Stat::make('Thành viên nhóm', $teamMembersCount)
                ->description('Số lượng người trong các dự án của bạn')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
