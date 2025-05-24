<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsersController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): View
    {
        $users = User::paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $storeUserService = new \App\Services\User\StoreUserService();
        $storeUserService->execute($validated);
        
        return redirect()->route('admin.users')
            ->with('success', 'ユーザーが正常に作成されました。');
    }
    
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        
        $updateUserService = new \App\Services\User\UpdateUserService();
        $updateUserService->execute($user, $validated);
        
        return redirect()->route('admin.users')
            ->with('success', 'ユーザーが正常に更新されました。');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $deleteUserService = new \App\Services\User\DeleteUserService();
        $deleteUserService->execute($user);
        
        return redirect()->route('admin.users')
            ->with('success', 'ユーザーが正常に削除されました。');
    }
}
