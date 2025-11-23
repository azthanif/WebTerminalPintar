<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Admin\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        protected UserRepository $users
    ) {}

    public function getIndexData(array $filters): array
    {
        $query = User::query()->with('roles');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        $stats = [
            'total'      => User::count(),
            'aktif'      => User::where('is_active', true)->count(),
            'admin_guru' => User::role(['admin', 'guru'])->count(),
            'orang_tua'  => User::role('ortu')->count(),
        ];

        return [
            'users'   => $users,
            'stats'   => $stats,
            'filters' => $filters,
        ];
    }

    public function create(array $data): User
    {
        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        $data['password'] = bcrypt($data['password']);

        $user = $this->users->create($data);

        $this->syncRole($user, $roleId);

        return $user->load('roles');
    }

    public function update(User $user, array $data): User
    {
        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $updated = $this->users->update($user, $data);

        $this->syncRole($updated, $roleId);

        return $updated->load('roles');
    }

    public function delete(User $user): void
    {
        $this->users->delete($user);
    }

    protected function syncRole(User $user, ?int $roleId): void
    {
        if (! $roleId) {
            $user->syncRoles([]);
            return;
        }

        $role = Role::find($roleId);

        if ($role) {
            $user->syncRoles([$role->name]);
        }
    }
}
