<?php

namespace App\Policies\OrangTua;

use App\Models\User;

class ParentDashboardPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('ortu');
    }

    public function manageNotes(User $user): bool
    {
        return $this->view($user);
    }

    public function viewSchedules(User $user): bool
    {
        return $this->view($user);
    }
}
