<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoSession extends Model
{
    protected $fillable = ['name', 'status', 'started_at', 'ended_at', 'created_by', 'description'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'session_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(SessionSnapshot::class, 'session_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SoEntry::class, 'session_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
