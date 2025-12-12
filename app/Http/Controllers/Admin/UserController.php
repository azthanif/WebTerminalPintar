<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Http\Resources\Admin\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only('search', 'per_page');

        $data = $this->userService->getIndexData($filters);

        return Inertia::render('Admin/Users/Index', [
            'users'   => UserResource::collection($data['users']),
            'stats'   => $data['stats'],
            'filters' => $filters,
            'currentUserId' => $request->user()->id,
            'title'   => 'Manajemen Pengguna',
        ]);
    }

    public function create()
    {
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('Admin/Users/Form', [
            'roles' => RoleResource::collection($roles)->resolve(),
            'user'  => null,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('Admin/Users/Form', [
            'roles' => RoleResource::collection($roles)->resolve(),
            'user'  => (new UserResource($user->load('roles')))->resolve(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $this->userService->delete($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
