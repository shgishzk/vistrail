<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StoreUserService
{
    /**
     * Create a new user with the given data.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function execute(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        
        return User::create($data);
    }
}
