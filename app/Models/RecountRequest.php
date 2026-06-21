<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecountRequest extends Model
{
    protected $fillable = [
        'so_entry_id', 'requested_by', 'assigned_team_id',
        'assigned_petugas_id', 'status', 'notes',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(SoEntry::class, 'so_entry_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function assignedPetugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_petugas_id');
    }
}
