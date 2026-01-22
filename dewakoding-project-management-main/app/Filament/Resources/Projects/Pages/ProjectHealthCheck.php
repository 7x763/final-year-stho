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

    protected static string $view = 'filament.resources.projects.pages.project-health-check';

    public $analysis;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        $service = new ProjectHealthService();
        $this->analysis = $service->analyze($this->record->id);
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
