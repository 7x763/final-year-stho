<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * List tickets for a specific project.
     * GET /api/projects/{projectId}/tickets
     */
    public function index(Request $request, $projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if (!$request->user()->canAccessProject($project)) {
             return response()->json(['message' => 'Forbidden'], 403);
        }

        $tickets = $project->tickets()->with('status', 'assignees', 'priority')->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Create a new ticket.
     * POST /api/tickets
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'ticket_status_id' => 'required|exists:ticket_statuses,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $project = Project::find($validated['project_id']);
        if (!$request->user()->canAccessProject($project)) {
             return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validate status belongs to project
        if (!$project->ticketStatuses()->where('id', $validated['ticket_status_id'])->exists()) {
             return response()->json(['message' => 'Invalid status for this project'], 422);
        }

        $validated['created_by'] = $request->user()->id;
        
        $ticket = Ticket::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Update a ticket status.
     * PUT /api/tickets/{id}
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        // Simple auth check similar to existing checks
        $user = $request->user();
        $isMember = DB::table('project_users')
            ->where('project_id', $ticket->project_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$user->isSuperAdmin() && !$isMember) {
             return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'ticket_status_id' => 'sometimes|exists:ticket_statuses,id',
            'name' => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data' => $ticket->fresh(['status'])
        ]);
    }
}
