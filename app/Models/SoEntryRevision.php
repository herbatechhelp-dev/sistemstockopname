<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoEntryRevision extends Model
{
    protected $fillable = [
        'so_entry_id', 'changed_by', 'old_fisik_qty', 'new_fisik_qty',
        'reason', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_fisik_qty' => 'decimal:2',
            'new_fisik_qty' => 'decimal:2',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(SoEntry::class, 'so_entry_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
