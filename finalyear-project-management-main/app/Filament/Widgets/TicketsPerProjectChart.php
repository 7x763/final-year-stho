<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class TicketsPerProjectChart extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Số lượng vé hỗ trợ theo dự án';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        // Query projects based on user role
        $projectsQuery = Project::query()
            ->withCount('tickets')
            ->orderBy('name');

        if (! $isSuperAdmin) {
            $projectsQuery->whereHas('members', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            });
        }

        $projects = $projectsQuery->get();

        // Prepare data for chart
        $labels = $projects->pluck('name')->toArray();
        $data = $projects->pluck('tickets_count')->toArray();

        // Generate colors for each bar
        $colors = [];
        $baseColors = [
            '#3B82F6', // Blue
            '#10B981', // Green
            '#F59E0B', // Yellow
            '#EF4444', // Red
            '#8B5CF6', // Purple
            '#06B6D4', // Cyan
            '#F97316', // Orange
            '#84CC16', // Lime
            '#EC4899', // Pink
            '#6B7280', // Gray
        ];

        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số lượng vé',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
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
                    'display' => false,
                    'position' => 'right',
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
