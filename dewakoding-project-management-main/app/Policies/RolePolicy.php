<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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
        return $authUser->permissions()->where('name', 'view_any_role')->exists();
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'view_role')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_role')->exists();
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'update_role')->exists();
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'delete_role')->exists();
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'restore_role')->exists();
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_role')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_role')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_role')->exists();
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->permissions()->where('name', 'replicate_role')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_role')->exists();
    }

}