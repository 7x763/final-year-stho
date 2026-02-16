<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectHealthService;
use Illuminate\Http\Request;

class ProjectHealthController extends Controller
{
    protected $service;

    public function __construct(ProjectHealthService $service)
    {
        $this->service = $service;
    }

    /**
     * Analyze project health.
     * 
     * POST /api/projects/{id}/analyze
     */
    public function analyze(Request $request, int $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Check if user has permission (optional, skipping for now as per context)
        // if (!$request->user()->can('view', $project)) { ... }

        $force = $request->input('force', false);
        
        try {
            $result = $this->service->analyze($id, $force);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
