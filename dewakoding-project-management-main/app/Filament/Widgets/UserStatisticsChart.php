<?php

namespace App\Filament\Widgets;

use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class UserStatisticsChart extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Thống kê người dùng';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $users = User::query()
            ->when(! auth()->user()->hasRole('super_admin'), function ($query): void {
                $query->where('id', auth()->id());
            })
            ->withCount([
                'projects as total_projects',
                'assignedTickets as total_assigned_tickets',
            ])
            ->orderBy('name')
            ->get();

        $labels = $users->pluck('name')->toArray();
        $projectsData = $users->pluck('total_projects')->toArray();
        $ticketsData = $users->pluck('total_assigned_tickets')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tổng số dự án',
                    'data' => $projectsData,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Số vé được giao',
                    'data' => $ticketsData,
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#10B981',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
