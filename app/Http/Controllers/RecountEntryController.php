<?php

namespace App\Http\Controllers;

use App\Models\RecountRequest;
use App\Models\SoEntry;
use App\Models\SoEntryRevision;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamLocationAllocation;
use App\Models\AuditLog;
use App\Services\SessionContext;
use Illuminate\Http\Request;

class RecountEntryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeSession = SessionContext::resolve($user);
        if (!$activeSession) {
            return view('recount.index', ['session'=>null, 'recounts'=>collect()]);
        }

        $recounts = RecountRequest::with(['entry.item', 'entry.location', 'assignedTeam', 'requester'])
            ->where('assigned_petugas_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('entry', fn($q)=>$q->where('session_id', $activeSession->id))
            ->orderBy('created_at','desc')
            ->get();

        $team = $this->getUserTeam($user, $activeSession);

        return view('recount.index', [
            'session'=>$activeSession,
            'team'=>$team,
            'recounts'=>$recounts,
        ]);
    }

    public function show(RecountRequest $recount)
    {
        $user = auth()->user();
        if ($recount->assigned_petugas_id !== $user->id) {
            abort(403, 'Bukan tugas recount Anda.');
        }
        $recount->load(['entry.item.category','entry.location','entry.petugas','assignedTeam','requester']);
        return view('recount.show', compact('recount'));
    }

    public function submit(Request $request, RecountRequest $recount)
    {
        $user = auth()->user();
        if ($recount->assigned_petugas_id !== $user->id) {
            abort(403);
        }
        if ($recount->status !== 'pending') {
            return back()->with('error','Recount sudah selesai.');
        }

        $data = $request->validate([
            'fisik_qty' => ['required','numeric','min:0','regex:/^\d+(\.\d{1,2})?$/'],
            'keterangan' => 'nullable|string',
            'batch_code' => 'nullable|string|max:100',
        ], [
            'fisik_qty.regex' => 'Kuantitas fisik maksimal 2 angka di belakang koma.',
            'fisik_qty.min' => 'Kuantitas fisik tidak boleh kurang dari nol.',
        ]);

        if ($data['fisik_qty']==0 && empty($data['keterangan'])) {
            return back()->withErrors(['keterangan'=>'Keterangan wajib diisi jika qty 0.']);
        }

        $entry = $recount->entry;
        $activeSession = $entry->session;

        // verify assigned team still owns location? allow recount even if not allocated to assigned team, but lokasi must be valid
        $team = Team::find($recount->assigned_team_id);

        $oldQty = $entry->fisik_qty;

        // Create revision history
        SoEntryRevision::create([
            'so_entry_id'=>$entry->id,
            'changed_by'=>auth()->id(),
            'old_fisik_qty'=>$oldQty,
            'new_fisik_qty'=>$data['fisik_qty'],
            'reason'=>'Recount oleh petugas '.(auth()->user()->full_name ?? auth()->user()->name),
            'ip_address'=>$request->ip(),
            'user_agent'=>$request->userAgent(),
        ]);

        $entry->update([
            'fisik_qty'=>$data['fisik_qty'],
            'keterangan'=>$data['keterangan'] ?? $entry->keterangan,
            'batch_code'=>$data['batch_code'] ?? $entry->batch_code,
            'status'=>'recount_done',
        ]);

        $recount->update(['status'=>'completed']);

        AuditLog::log('recount_submitted', RecountRequest::class, $recount->id, ['old_fisik_qty'=>$oldQty], ['new_fisik_qty'=>$data['fisik_qty']]);
        AuditLog::log('recount_entry_updated', SoEntry::class, $entry->id, ['fisik_qty'=>$oldQty], ['fisik_qty'=>$data['fisik_qty']]);

        return redirect('/recount')->with('success','Recount berhasil disimpan. Qty diperbarui.');
    }

    private function getUserTeam($user, $session): ?Team
    {
        if ($user->role === 'team_leader') {
            return Team::where('session_id', $session->id)->where('team_leader_id', $user->id)->first();
        }
        $membership = TeamMember::where('user_id', $user->id)->whereHas('team', fn($q)=>$q->where('session_id', $session->id))->first();
        return $membership?->team;
    }
}
