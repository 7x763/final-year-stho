<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeProjectHealthJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $projectId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $project = \App\Models\Project::find($this->projectId);
        
        if (!$project) {
            return;
        }

        $project->update(['ai_analysis_status' => 'processing']);

        try {
            $service = new \App\Services\ProjectHealthService();
            // Call internal method directly to avoid circular dependency or redundant logic
            // We need to publicize getAiAnalysis or replicate logic. 
            // Better: Service::analyze should return array, we extract summary.
            
            // To do this cleanly, we'll modify the Service to separate calculation from AI generation
            // But for now, let's just call analyze, which returns the array INCLUDING ai_summary if we keep it there.
            // Wait, analyze() inside Service calls getAiAnalysis (sync). We want to move that sync call HERE.
            
            $analysisData = $service->calculateStats($this->projectId); // We will create this method
            $aiSummary = $service->getAiAnalysis($analysisData); // We will expose this method

            $project->update([
                'ai_analysis' => $aiSummary,
                'ai_analysis_status' => 'completed',
                'ai_analysis_at' => now(),
            ]);

        } catch (\Exception $e) {
            $project->update([
                'ai_analysis_status' => 'failed',
                'ai_analysis' => 'Lỗi: ' . $e->getMessage(),
            ]);
        }
    }
}
