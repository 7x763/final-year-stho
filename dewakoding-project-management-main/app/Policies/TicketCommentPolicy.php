<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TicketComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketCommentPolicy
{
    use HandlesAuthorization;
    
    public function before(AuthUser $authUser, $ability)
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'view_any_ticket::comment')->exists();
    }

    public function view(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'view_ticket::comment')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_ticket::comment')->exists();
    }

    public function update(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'update_ticket::comment')->exists();
    }

    public function delete(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'delete_ticket::comment')->exists();
    }

    public function restore(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'restore_ticket::comment')->exists();
    }

    public function forceDelete(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_ticket::comment')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_ticket::comment')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_ticket::comment')->exists();
    }

    public function replicate(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->permissions()->where('name', 'replicate_ticket::comment')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_ticket::comment')->exists();
    }

}