<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class StoreAdminService
{
    public function execute(array $data): Admin
    {
        $data['password'] = Hash::make($data['password']);

        return Admin::create($data);
    }
}
