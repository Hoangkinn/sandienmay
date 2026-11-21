<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\ActivityLoggable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, ActivityLoggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'role' => Role::class,
    ];

    public function toActivityArray()
    {
        $array = $this->toArray();
        unset($array['password'], $array['remember_token']);
        return $array;
    }

    /**
     * Lấy role chính ra để hiển thị (tương thích cả dữ liệu cũ và mới)
     */
    public function getRoleMainAttribute()
    {
        $data = json_decode($this->attributes['role'] ?? '', true);
        return is_array($data) ? ($data['role'] ?? $this->attributes['role']) : $this->attributes['role'];
    }

    /**
     * Lấy danh sách các quyền chi tiết đã được tích chọn
     */
    public function getPermissionsAttribute()
    {
        $data = json_decode($this->attributes['role'] ?? '', true);
        return is_array($data) ? ($data['permissions'] ?? []) : [];
    }

    /**
     * Kiểm tra xem user có quyền cụ thể nào đó không
     */
    public function hasPermission($permissionKey)
    {
        $roleMain = $this->role_main;

        // Nếu là admin hoặc super_admin thì có toàn quyền
        if (in_array($roleMain, ['admin', 'super_admin'])) {
            return true;
        }

        // Kiểm tra xem quyền đó có nằm trong mảng permissions không
        $permissions = $this->permissions;
        return in_array($permissionKey, $permissions);
    }
}
