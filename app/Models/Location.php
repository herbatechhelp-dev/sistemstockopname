<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'warehouse', 'block', 'rack', 'row', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TeamLocationAllocation::class);
    }

    public function soEntries(): HasMany
    {
        return $this->hasMany(SoEntry::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(SessionSnapshot::class);
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([$this->warehouse, $this->block, $this->rack, $this->row]);
        return implode(' - ', $parts);
    }
}
