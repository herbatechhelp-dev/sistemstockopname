<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'full_name', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Teams where this user is the leader
    public function ledTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'team_leader_id');
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_leader_id');
    }

    // Get user's team in a session (as TL or member)
    public function getActiveTeam(?int $sessionId = null)
    {
        // If TL, find team where they lead
        if ($this->role === 'team_leader') {
            return Team::where('team_leader_id', $this->id)
                ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
                ->whereHas('session', fn($q) => $q->where('status', 'active'))
                ->first();
        }
        // If petugas, find team via membership
        $membership = TeamMember::where('user_id', $this->id)
            ->when($sessionId, fn($q) => $q->where('team_id', Team::select('id')->where('session_id', $sessionId)))
            ->whereHas('team.session', fn($q) => $q->where('status', 'active'))
            ->first();
        return $membership?->team;
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function soEntries(): HasMany
    {
        return $this->hasMany(SoEntry::class, 'petugas_id');
    }

    public function createdSessions(): HasMany
    {
        return $this->hasMany(SoSession::class, 'created_by');
    }

    // Helpers
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdminOrSuperadmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function isTeamLeader(): bool
    {
        return $this->role === 'team_leader';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas_so';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?? $this->name;
    }
}
