<?php

namespace App\Observers;

use App\Models\SoEntry;
use App\Models\AuditLog;

class SoEntryObserver
{
    /**
     * Handle the SoEntry "created" event.
     */
    public function created(SoEntry $entry): void
    {
        AuditLog::log('entry_created', SoEntry::class, $entry->id, null, [
            'item_id' => $entry->item_id,
            'location_id' => $entry->location_id,
            'fisik_qty' => $entry->fisik_qty,
            'batch_code' => $entry->batch_code,
            'petugas_id' => $entry->petugas_id,
        ]);
    }

    /**
     * Handle the SoEntry "updated" event.
     */
    public function updated(SoEntry $entry): void
    {
        $changes = $entry->getChanges();
        $original = [];
        $new = [];

        foreach ($changes as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at'])) continue;
            $original[$key] = $entry->getOriginal($key);
            $new[$key] = $value;
        }

        if (!empty($changes)) {
            AuditLog::log('entry_updated', SoEntry::class, $entry->id, $original, $new);
        }
    }

    /**
     * Handle the SoEntry "deleted" event.
     */
    public function deleted(SoEntry $entry): void
    {
        AuditLog::log('entry_deleted', SoEntry::class, $entry->id, [
            'item_id' => $entry->item_id,
            'location_id' => $entry->location_id,
            'fisik_qty' => $entry->fisik_qty,
        ], null);
    }
}
