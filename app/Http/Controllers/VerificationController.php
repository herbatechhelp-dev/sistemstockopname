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
        // Akses: TL hanya boleh lihat entry timnya & sesi terpilih
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);
        if ($activeSession && $entry->session_id !== $activeSession->id) {
            abort(403, 'Entry bukan dari sesi terpilih.');
        }
        if ($entry->team->team_leader_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat entry tim lain.');
        }
        $entry->load(['item', 'location', 'petugas', 'revisions']);
        return view('verification.show', compact('entry'));
    }

    // TL edits entry (only pending)
    public function update(Request $request, SoEntry $entry)
    {
        // Guard: entry harus milik tim TL & sesi terpilih
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);
        if ($activeSession && $entry->session_id !== $activeSession->id) {
            abort(403, 'Entry bukan dari sesi terpilih.');
        }
        if ($entry->team->team_leader_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengedit entry tim lain.');
        }
        if ($entry->status !== 'pending') {
            return back()->with('error', 'Hanya data berstatus Pending yang dapat diedit.');
        }

        $data = $request->validate([
            'fisik_qty' => ['required','numeric','min:0','regex:/^\d+(\.\d{1,2})?$/'],
            'keterangan' => 'nullable|string',
            'batch_code' => 'nullable|string|max:100',
        ], [
            'fisik_qty.regex' => 'Kuantitas fisik maksimal 2 angka di belakang koma.',
            'fisik_qty.min' => 'Kuantitas fisik tidak boleh kurang dari nol.',
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
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);
        if ($activeSession && $entry->session_id !== $activeSession->id) {
            abort(403);
        }
        if ($entry->team->team_leader_id !== $user->id) {
            abort(403);
        }
        if ($entry->status !== 'pending') {
            return back()->with('error', 'Hanya data berstatus Pending yang dapat diverifikasi.');
        }

        $entry->update(['status' => 'verified']);
        AuditLog::log('verify_entry', SoEntry::class, $entry->id, ['status' => 'pending'], ['status' => 'verified']);
        $this->maybeCompleteAllocation($entry);

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

        // A4: cek alokasi yang semua entry-nya sudah verified -> completed
        $this->completeAllocationsForTeam($team, $activeSession);

        return back()->with('success', "{$count} data berhasil diverifikasi.");
    }

    private function maybeCompleteAllocation(SoEntry $entry): void
    {
        $allocation = \App\Models\TeamLocationAllocation::where('team_id', $entry->team_id)
            ->where('location_id', $entry->location_id)
            ->first();
        if (!$allocation || $allocation->status === 'completed') return;
        $hasPending = SoEntry::where('team_id', $entry->team_id)
            ->where('session_id', $entry->session_id)
            ->where('location_id', $entry->location_id)
            ->where('status', 'pending')
            ->exists();
        if (!$hasPending) {
            $allocation->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }

    private function completeAllocationsForTeam(Team $team, SoSession $session): void
    {
        $allocs = \App\Models\TeamLocationAllocation::where('team_id', $team->id)->where('status', '!=', 'completed')->get();
        foreach ($allocs as $alloc) {
            $hasPending = SoEntry::where('team_id', $team->id)
                ->where('session_id', $session->id)
                ->where('location_id', $alloc->location_id)
                ->where('status', 'pending')
                ->exists();
            if (!$hasPending) {
                $alloc->update(['status' => 'completed', 'completed_at' => now()]);
            }
        }
    }
}
