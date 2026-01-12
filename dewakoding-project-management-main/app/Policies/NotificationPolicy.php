<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
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
        return $authUser->permissions()->where('name', 'view_any_notification')->exists();
    }

    public function view(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'view_notification')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_notification')->exists();
    }

    public function update(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'update_notification')->exists();
    }

    public function delete(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'delete_notification')->exists();
    }

    public function restore(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'restore_notification')->exists();
    }

    public function forceDelete(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_notification')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_notification')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_notification')->exists();
    }

    public function replicate(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->permissions()->where('name', 'replicate_notification')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_notification')->exists();
    }

}