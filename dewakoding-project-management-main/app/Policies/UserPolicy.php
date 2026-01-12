<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $authUser->permissions()->where('name', 'view_any_user')->exists();
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'view_user')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_user')->exists();
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'update_user')->exists();
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'delete_user')->exists();
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_user')->exists();
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_user')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_user')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_user')->exists();
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'replicate_user')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_user')->exists();
    }

}