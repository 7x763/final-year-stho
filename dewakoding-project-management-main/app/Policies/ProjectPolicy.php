<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
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
        return true;
    }

    public function view(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->permissions()->where('name', 'view_project')->exists()) {
            return true;
        }

        return $project->members()->where('users.id', $authUser->id)->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_project')->exists();
    }

    public function update(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->permissions()->where('name', 'update_project')->exists()) {
            return true;
        }

        return $project->members()->where('users.id', $authUser->id)->exists();
    }

    public function delete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->permissions()->where('name', 'delete_project')->exists();
    }

    public function restore(AuthUser $authUser, Project $project): bool
    {
        return $authUser->permissions()->where('name', 'restore_project')->exists();
    }

    public function forceDelete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_project')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_project')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_project')->exists();
    }

    public function replicate(AuthUser $authUser, Project $project): bool
    {
        return $authUser->permissions()->where('name', 'replicate_project')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_project')->exists();
    }

}
