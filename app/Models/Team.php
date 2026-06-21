<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'session_id', 'team_leader_id'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SoSession::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function locationAllocations(): HasMany
    {
        return $this->hasMany(TeamLocationAllocation::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SoEntry::class);
    }
}
