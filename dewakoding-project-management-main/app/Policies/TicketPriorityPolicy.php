<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TicketPriority;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPriorityPolicy
{
    use HandlesAuthorization;
    
    public function before(AuthUser $authUser, $ability)
    {
        if ($authUser->roles()->where('name', 'super_admin')->exists()) {
            return true;
        }
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'view_any_ticket::priority')->exists();
    }

    public function view(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'view_ticket::priority')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_ticket::priority')->exists();
    }

    public function update(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'update_ticket::priority')->exists();
    }

    public function delete(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'delete_ticket::priority')->exists();
    }

    public function restore(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'restore_ticket::priority')->exists();
    }

    public function forceDelete(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_ticket::priority')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_ticket::priority')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_ticket::priority')->exists();
    }

    public function replicate(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->permissions()->where('name', 'replicate_ticket::priority')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_ticket::priority')->exists();
    }

}