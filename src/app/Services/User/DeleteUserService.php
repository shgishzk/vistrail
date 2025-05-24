<?php

namespace App\Services\User;

use App\Models\User;

class DeleteUserService
{
    /**
     * Delete the specified user.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function execute(User $user): bool
    {
        return $user->delete();
    }
}
