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

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $users = User::query()
            ->when(! auth()->user()->hasRole('super_admin'), function ($query): void {
                $query->where('id', auth()->id());
            })
            ->withCount('assignedTickets as total_assigned_tickets')
            ->orderByDesc('total_assigned_tickets')
            ->limit(10) // Limit to top 10 active users to keep chart clean
            ->get();

        $labels = $users->pluck('name')->toArray();
        $data = $users->pluck('total_assigned_tickets')->toArray();

        // Color Palette
        $colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', 
            '#06B6D4', '#F97316', '#84CC16', '#EC4899', '#6B7280'
        ];
        
        $backgroundColors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $backgroundColors[] = $colors[$i % count($colors)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Vé được giao',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'cutout' => '50%',
        ];
    }
}
