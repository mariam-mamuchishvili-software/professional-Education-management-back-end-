<?php

namespace App\Policies;

use App\Models\TeacherWorkExperience;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Teachers manage only their own work experience (through the API's "me" endpoints), while
 * administrators manage everyone's, including through the admin panel. Another
 * teacher's record is reported as not found, so its existence is not revealed.
 */
class TeacherWorkExperiencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeacherWorkExperience $workExperience): Response
    {
        return $this->adminOrOwner($user, $workExperience);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->teacher !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeacherWorkExperience $workExperience): Response
    {
        return $this->adminOrOwner($user, $workExperience);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeacherWorkExperience $workExperience): Response
    {
        return $this->adminOrOwner($user, $workExperience);
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TeacherWorkExperience $workExperience): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TeacherWorkExperience $workExperience): bool
    {
        return $user->isAdmin();
    }

    private function adminOrOwner(User $user, TeacherWorkExperience $workExperience): Response
    {
        if ($user->isAdmin() || ($user->teacher !== null && $user->teacher->id === $workExperience->teacher_id)) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
