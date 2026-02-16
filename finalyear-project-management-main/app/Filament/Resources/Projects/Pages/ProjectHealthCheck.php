<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\ProjectHealthService;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ProjectHealthCheck extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProjectResource::class;

    protected string $view = 'filament.resources.projects.pages.project-health-check';

    public $analysis;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        $this->refreshAnalysis();
    }

    public function refreshAnalysis(bool $force = false): void
    {
        $service = new ProjectHealthService();
        $this->analysis = $service->analyze($this->record->id, $force);
    }

    public function getListeners(): array
    {
        return [
            'refresh' => '$refresh',
        ];
    }
    
    public function analyzeAction(): void
    {
        $this->refreshAnalysis(true);
        
        \Filament\Notifications\Notification::make()
            ->title('Đã yêu cầu phân tích')
            ->body('Hệ thống đang xử lý, vui lòng đợi giây lát...')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return "Kiểm tra sức khỏe dự án: {$this->record->name}";
    }

    public function getHeading(): string
    {
        return "AI Project Health Check";
    }
}
