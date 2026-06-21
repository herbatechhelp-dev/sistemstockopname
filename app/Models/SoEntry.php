<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoEntry extends Model
{
    protected $fillable = [
        'session_id', 'team_id', 'petugas_id', 'item_id', 'location_id',
        'uom', 'batch_code', 'fisik_qty', 'keterangan', 'status',
        'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['fisik_qty' => 'decimal:2'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SoSession::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SoEntryRevision::class);
    }

    public function recountRequests(): HasMany
    {
        return $this->hasMany(RecountRequest::class);
    }

    public function getSnapshot(): ?SessionSnapshot
    {
        return SessionSnapshot::where('session_id', $this->session_id)
            ->where('item_id', $this->item_id)
            ->where('location_id', $this->location_id)
            ->first();
    }

    public function getVarianceAttribute(): ?float
    {
        $snapshot = $this->getSnapshot();
        if (!$snapshot) return null;
        return (float) $this->fisik_qty - (float) $snapshot->system_qty;
    }

    public function getVarianceStatusAttribute(): string
    {
        $variance = $this->variance;
        if ($variance === null) return 'unknown';
        if ($variance == 0) return 'match';
        $snapshot = $this->getSnapshot();
        if (!$snapshot || $snapshot->system_qty == 0) return 'unacceptable';
        $percentage = abs($variance) / $snapshot->system_qty * 100;
        $tolerance = (float) (SystemSetting::where('key', 'variance_tolerance_percentage')->value('value') ?? 1);
        return $percentage <= $tolerance ? 'tolerable' : 'unacceptable';
    }
}
