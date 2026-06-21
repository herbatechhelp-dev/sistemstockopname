<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionSnapshot extends Model
{
    protected $fillable = ['session_id', 'item_id', 'location_id', 'system_qty'];

    protected function casts(): array
    {
        return ['system_qty' => 'decimal:2'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SoSession::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
