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
        if ($authUser->hasRole('super_admin')) {
            return true;
        }
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
    }

    public function view(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->can('view_project')) {
            return true;
        }

        return $project->members()->where('users.id', $authUser->id)->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_project');
    }

    public function update(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->can('update_project')) {
            return true;
        }

        return $project->members()->where('users.id', $authUser->id)->exists();
    }

    public function delete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('delete_project');
    }

    public function restore(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('restore_project');
    }

    public function forceDelete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('force_delete_project');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_project');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_project');
    }

    public function replicate(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('replicate_project');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_project');
    }

}
