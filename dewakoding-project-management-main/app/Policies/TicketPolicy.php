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
        if ($user->roles()->where('name', 'super_admin')->exists()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        // If before() returns true, this isn't even reached for super_admin
        if ($user->permissions()->where('name', 'view_any_ticket')->exists()) {
            return true;
        }

        // Allow viewing if user is associated with any project as a member
        return $user->projects()->exists();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->permissions()->where('name', 'view_ticket')->exists()) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees()->where('users.id', $user->id)->exists() ||
               ($ticket->project && $ticket->project->members()->where('users.id', $user->id)->exists());
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->permissions()->where('name', 'update_ticket')->exists()) {
            return true;
        }

        return $ticket->created_by == $user->id ||
               $ticket->assignees()->where('users.id', $user->id)->exists() ||
               ($ticket->project && $ticket->project->members()->where('users.id', $user->id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->permissions()->where('name', 'create_ticket')->exists();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->permissions()->where('name', 'delete_ticket')->exists();
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
