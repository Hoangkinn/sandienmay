<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminInfo;
use App\Models\BaseModel;
use App\Models\Role;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function request;

class AuthService
{
    private Guard $auth;
    private ?object $fakeUser = null;

    public function __construct(string $guard = 'admin')
    {
        $this->auth = Auth::guard($guard);
    }

    public function login(array $credentials, bool $remember = false): bool
    {
        if ($bool = $this->auth->attempt($credentials, $remember)) {
            session(['admin_id' => $this->auth->id()]);
            request()->session()->regenerate();
        }
        return $bool;
    }

    public function logout(): void
    {
        $this->auth->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function user()
    {
        return $this->fakeUser ?? $this->auth->user()->load('permissions');
    }

    public function check(): bool
    {
        return $this->id() > 0;
    }

    public function id(): int
    {
        return session('admin_id', 0);
    }
    public function roleHasPermission(int|Role $role_id, string|BaseModel $modelClassNam, string $action): bool
    {
        if (is_int($role_id)) {
            $role = Role::query()->find($role_id);
        } else {
            $role = $role_id;
        }
        if (! is_string($modelClassNam))
            $modelClassNam = $modelClassNam::class;
        return $role->whereHas('permissions', fn(Builder $q) => $q->where('model', $modelClassNam)->where('slug', $action))->exists();
    }

    public function hasPermission(string|BaseModel|array $modelClassName, string|array $permission = 'view', ?Admin $admin = null): bool
    {
        if (! $admin){
            /** @var Admin $user */
            $user = $this->user();
        } else {
            $user = $admin;
        }

        $userPermissions = $user->permissions;

        // Chuẩn hóa model thành mảng tên class
        $models = is_array($modelClassName)
            ? array_map(fn($m) => is_string($m) ? $m : $m::class, $modelClassName)
            : [is_string($modelClassName) ? $modelClassName : $modelClassName::class];

        // Chuẩn hóa permission thành mảng
        $permissions = (array) $permission;

        foreach ($models as $model) {
            $modelPermissions = $userPermissions->where('model', $model);

            // Model này thiếu bất kỳ permission nào → fail
            foreach ($permissions as $perm) {
                if (! $modelPermissions->where('slug', $perm)->isNotEmpty()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function checkPermission(string|BaseModel $modelClassName, string $permission): void
    {
        abort_unless($this->hasPermission($modelClassName, $permission), 403, 'Đừng táy máy');
    }
    public function hasOwn(?BaseModel $model): bool
    {
        return match (true){
            $model instanceof AdminInfo => $model->id == $this->id(),
            ! empty($model) => $model->admin_id == $this->id(),
            default => false
        };
    }
    public function canEditAdminInfo(?AdminInfo $adminInfo): bool
    {
        return $this->hasPermission(Admin::class, 'edit')  || $this->hasPermission(AdminInfo::class, 'edit') || $this->hasOwn($adminInfo);
    }

    /**
     * Gán user giả để dùng cho các tác vụ hệ thống
     */
    public function setFakeUser(string $name, string $email): void
    {
        $this->fakeUser = (object)[
            'name' => $name,
            'email' => $email,
        ];
    }
}
