<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminsController extends Controller
{
    public function index(): View
    {
        $admins = Admin::orderBy('name')->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $storeAdminService = new \App\Services\Admin\StoreAdminService();
        $storeAdminService->execute($validated);

        return redirect()->route('admin.admins')
            ->with('success', __('Admin created successfully.'));
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(UpdateAdminRequest $request, Admin $admin): RedirectResponse
    {
        $validated = $request->validated();

        $updateAdminService = new \App\Services\Admin\UpdateAdminService();
        $updateAdminService->execute($admin, $validated);

        return redirect()->route('admin.admins')
            ->with('success', __('Admin updated successfully.'));
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $deleteAdminService = new \App\Services\Admin\DeleteAdminService();
        $deleteAdminService->execute($admin);

        return redirect()->route('admin.admins')
            ->with('success', __('Admin deleted successfully.'));
    }
}
