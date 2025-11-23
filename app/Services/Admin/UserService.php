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
        $data['password'] = bcrypt($data['password']);

        return $this->users->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->users->update($user, $data);
    }

    public function delete(User $user): void
    {
        $this->users->delete($user);
    }
}
