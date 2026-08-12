<?php

namespace App\Http\Controllers;

use App\Models\SoEntry;
use App\Models\SoEntryRevision;
use App\Models\SoSession;
use App\Models\Team;
use App\Models\AuditLog;
use App\Services\SessionContext;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);

        if (!$activeSession) {
            return view('verification.index', ['session' => null, 'entries' => collect()]);
        }

        // Get team led by this TL in the selected session
        $team = Team::where('session_id', $activeSession->id)
            ->where('team_leader_id', $user->id)
            ->first();

        if (!$team) {
            return view('verification.index', ['session' => $activeSession, 'team' => null, 'entries' => collect()]);
        }

        $entries = SoEntry::where('team_id', $team->id)
            ->where('session_id', $activeSession->id)
            ->with(['item', 'location', 'petugas'])
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('verification.index', [
            'session' => $activeSession,
            'team' => $team,
            'entries' => $entries,
        ]);
    }

    public function show(SoEntry $entry)
    {
        $entry->load(['item', 'location', 'petugas', 'revisions']);
        return view('verification.show', compact('entry'));
    }

    // TL edits entry (only pending)
    public function update(Request $request, SoEntry $entry)
    {
        if ($entry->status !== 'pending') {
            return back()->with('error', 'Hanya data berstatus Pending yang dapat diedit.');
        }

        $data = $request->validate([
            'fisik_qty' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'batch_code' => 'nullable|string|max:100',
        ]);

        // Validate: if qty is 0, keterangan is mandatory
        if ($data['fisik_qty'] == 0 && empty($data['keterangan'])) {
            return back()->withErrors(['keterangan' => 'Keterangan wajib diisi jika kuantitas fisik bernilai 0.']);
        }

        $oldQty = $entry->fisik_qty;
        $entry->update([
            'fisik_qty' => $data['fisik_qty'],
            'keterangan' => $data['keterangan'] ?? $entry->keterangan,
            'batch_code' => $data['batch_code'] ?? $entry->batch_code,
        ]);

        // Log revision
        SoEntryRevision::create([
            'so_entry_id' => $entry->id,
            'changed_by' => auth()->id(),
            'old_fisik_qty' => $oldQty,
            'new_fisik_qty' => $data['fisik_qty'],
            'reason' => 'Edit oleh Team Leader',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        AuditLog::log('edit_entry', SoEntry::class, $entry->id, ['fisik_qty' => $oldQty], ['fisik_qty' => $data['fisik_qty']]);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    // TL verifies entry
    public function verify(SoEntry $entry)
    {
        if ($entry->status !== 'pending') {
            return back()->with('error', 'Hanya data berstatus Pending yang dapat diverifikasi.');
        }

        $entry->update(['status' => 'verified']);
        AuditLog::log('verify_entry', SoEntry::class, $entry->id, ['status' => 'pending'], ['status' => 'verified']);

        return back()->with('success', 'Data berhasil diverifikasi.');
    }

    // TL verifies all pending entries
    public function verifyAll(Request $request)
    {
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);

        if (!$activeSession) {
            return back()->with('error', 'Tidak ada sesi aktif untuk akun Anda.');
        }

        $team = Team::where('session_id', $activeSession->id)
            ->where('team_leader_id', $user->id)
            ->first();

        if (!$team) {
            return back()->with('error', 'Tim tidak ditemukan.');
        }

        $count = SoEntry::where('team_id', $team->id)
            ->where('session_id', $activeSession->id)
            ->where('status', 'pending')
            ->update(['status' => 'verified']);

        AuditLog::log('verify_all_entries', Team::class, $team->id, null, ['verified_count' => $count]);

        return back()->with('success', "{$count} data berhasil diverifikasi.");
    }
}
