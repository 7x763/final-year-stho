<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * List all projects visible to the user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $projects = Project::select('id', 'name', 'status', 'start_date', 'end_date')->get();
        } else {
            $projects = $user->projects()->select('projects.id', 'projects.name', 'projects.status', 'projects.start_date', 'projects.end_date')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Get project details.
     */
    public function show(Request $request, $id)
    {
        $project = Project::with(['members', 'tickets'])->find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Authorization check
        if (!$request->user()->canAccessProject($project)) {
             return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    /**
     * Create a new project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ticket_prefix' => 'required|string|max:5|unique:projects,ticket_prefix',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $project = Project::create([
                'name' => $validated['name'],
                'ticket_prefix' => strtoupper($validated['ticket_prefix']),
                'description' => $validated['description'] ?? null,
                'status' => 'active',
                'color' => '#3b82f6', // Default blue
            ]);

            // Add creator as member
            $project->members()->attach($request->user()->id, ['role' => 'owner']);

            // Create default statuses
            $project->ticketStatuses()->createMany([
                ['name' => 'To Do', 'color' => '#cbd5e1', 'sort_order' => 1, 'is_completed' => false],
                ['name' => 'In Progress', 'color' => '#3b82f6', 'sort_order' => 2, 'is_completed' => false],
                ['name' => 'Done', 'color' => '#22c55e', 'sort_order' => 3, 'is_completed' => true],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage()
            ], 500);
        }
    }
}
