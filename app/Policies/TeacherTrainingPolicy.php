<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\TeacherTraining;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Teachers manage only their own trainings and certificates (through the API's "me" endpoints and their cabinet), while
 * administrators manage everyone's, including through the admin panel. Another
 * teacher's record is reported as not found, so its existence is not revealed.
 */
class TeacherTrainingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Teacher $user): bool
    {
        return $user instanceof Teacher || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Teacher $user, TeacherTraining $training): Response
    {
        return $this->adminOrOwner($user, $training);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Teacher $user): bool
    {
        return $user instanceof Teacher || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Teacher $user, TeacherTraining $training): Response
    {
        return $this->adminOrOwner($user, $training);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Teacher $user, TeacherTraining $training): Response
    {
        return $this->adminOrOwner($user, $training);
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User|Teacher $user): bool
    {
        return $user instanceof Teacher || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Teacher $user, TeacherTraining $training): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Teacher $user, TeacherTraining $training): bool
    {
        return $this->isAdmin($user);
    }

    private function adminOrOwner(User|Teacher $user, TeacherTraining $training): Response
    {
        if ($this->isAdmin($user) || ($user instanceof Teacher && $user->id === $training->teacher_id)) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    private function isAdmin(User|Teacher $user): bool
    {
        return $user instanceof User && $user->isAdmin();
    }
}
