<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = ['session_id','item_id','location_id','system_qty','fisik_qty','adjustment_qty','created_by'];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:2',
            'fisik_qty' => 'decimal:2',
            'adjustment_qty' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(SoSession::class, 'session_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
