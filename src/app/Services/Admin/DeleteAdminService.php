<?php

namespace App\Services\Admin;

use App\Models\Admin;

class DeleteAdminService
{
    public function execute(Admin $admin): bool
    {
        return $admin->delete();
    }
}
