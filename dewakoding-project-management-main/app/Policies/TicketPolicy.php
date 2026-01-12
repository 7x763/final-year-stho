<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $user): bool
    {
        if ($user->can('view_any_ticket')) {
            return true;
        }

        // Allow viewing if user is associated with any project as a member
        return $user->projects()->exists();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->can('view_ticket')) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees()->where('users.id', $user->id)->exists() ||
               ($ticket->project && $ticket->project->members()->where('users.id', $user->id)->exists());
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->can('update_ticket')) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees()->where('users.id', $user->id)->exists() ||
               ($ticket->project && $ticket->project->members()->where('users.id', $user->id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->can('create_ticket');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete_ticket');
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->can('restore_ticket');
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->can('force_delete_ticket');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_ticket');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_ticket');
    }

    public function replicate(User $user, Ticket $ticket): bool
    {
        return $user->can('replicate_ticket');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_ticket');
    }
}
