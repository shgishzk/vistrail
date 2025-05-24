<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
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
}
