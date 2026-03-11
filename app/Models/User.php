<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'role',
        'organization',
        'region',
        'is_active',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'failed_login_attempts',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'collected_by');
    }

    public function validatedVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'validated_by');
    }

    public function vehicleHistories(): HasMany
    {
        return $this->hasMany(VehicleHistory::class, 'changed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Helpers
    public function isAgentCidec(): bool
    {
        return $this->role === 'agent_cidec';
    }

    public function isSupervisorCidec(): bool
    {
        return $this->role === 'supervisor_cidec';
    }

    public function isSupervisorSodeci(): bool
    {
        return $this->role === 'supervisor_sodeci';
    }

    public function isAdminSodeci(): bool
    {
        return $this->role === 'admin_sodeci';
    }

    public function isCidec(): bool
    {
        return $this->organization === 'CIDEC';
    }

    public function isSodeci(): bool
    {
        return $this->organization === 'SODECI';
    }

    public function isFinanceDbcg(): bool
    {
        return $this->role === 'finance_dbcg';
    }

    public function isFinanceDfc(): bool
    {
        return $this->role === 'finance_dfc';
    }

    public function isFinance(): bool
    {
        return in_array($this->role, ['finance_dbcg', 'finance_dfc']);
    }

    public function canEditFinancial(): bool
    {
        return in_array($this->role, ['admin_sodeci', 'finance_dbcg', 'finance_dfc']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'agent_cidec' => 'Agent CIDEC',
            'supervisor_cidec' => 'Superviseur CIDEC',
            'supervisor_sodeci' => 'Superviseur SODECI',
            'admin_sodeci' => 'Admin SODECI',
            'finance_dbcg' => 'Finance DBCG',
            'finance_dfc' => 'Finance DFC',
            default => $this->role,
        };
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
}
