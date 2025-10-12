<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_admin_roles')
            ->withPivot(['is_active', 'assigned_at', 'expires_at'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->is_active && $this->hasPermission('access_admin_panel');
    }

    public function canViewAnalytics(): bool
    {
        return $this->hasPermission('view_analytics');
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users');
    }

    public function canManageTeams(): bool
    {
        return $this->hasPermission('manage_teams');
    }

    public function canManagePlans(): bool
    {
        return $this->hasPermission('manage_plans');
    }

    public function canViewReports(): bool
    {
        return $this->hasPermission('view_reports');
    }
}
