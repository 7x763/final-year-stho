<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;
    
    public function before(User $user, string $ability): ?bool
    {
        // Direct check to avoid Gate/Policy recursion
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($user->can('view_any_ticket')) {
            return true;
        }

        return $user->projects()->exists();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->can('view_ticket')) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees->contains($user->id) ||
               ($ticket->project && $ticket->project->members->contains($user->id));
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->can('update_ticket')) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees->contains($user->id) ||
               ($ticket->project && $ticket->project->members->contains($user->id));
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
        return false; // Handled by before() for super_admin
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false; // Handled by before() for super_admin
    }

    public function forceDeleteAny(User $user): bool
    {
        return false; // Handled by before() for super_admin
    }

    public function restoreAny(User $user): bool
    {
        return false; // Handled by before() for super_admin
    }

    public function replicate(User $user, Ticket $ticket): bool
    {
        return false; // Handled by before() for super_admin
    }

    public function reorder(User $user): bool
    {
        return false; // Handled by before() for super_admin
    }
}
