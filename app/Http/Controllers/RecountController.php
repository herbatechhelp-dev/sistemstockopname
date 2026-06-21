<?php

namespace App\Http\Controllers;

use App\Models\RecountRequest;
use App\Models\SoEntry;
use App\Models\Team;
use App\Models\SoSession;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class RecountController extends Controller
{
    public function index(Request $request)
    {
        $recounts = RecountRequest::with(['entry.item', 'entry.location', 'entry.petugas', 'assignedTeam', 'assignedPetugas', 'requester'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.recounts.index', compact('recounts'));
    }

    public function store(Request $request, SoEntry $entry)
    {
        $data = $request->validate([
            'assigned_team_id' => 'required|exists:teams,id',
            'assigned_petugas_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Double-blind rule: assigned petugas must differ from original
        if ($data['assigned_petugas_id'] == $entry->petugas_id) {
            return back()->with('error', 'Petugas recount harus berbeda dari petugas asli (double-blind rule).');
        }

        // Check if recount already requested
        if ($entry->recountRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Recount sudah diminta untuk data ini.');
        }

        $recount = RecountRequest::create([
            'so_entry_id' => $entry->id,
            'requested_by' => auth()->id(),
            'assigned_team_id' => $data['assigned_team_id'],
            'assigned_petugas_id' => $data['assigned_petugas_id'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        $entry->update(['status' => 'recount_requested']);

        AuditLog::log('request_recount', RecountRequest::class, $recount->id, null, $data);

        return back()->with('success', 'Permintaan recount berhasil dibuat.');
    }

    public function complete(RecountRequest $recount)
    {
        $recount->update(['status' => 'completed']);
        $recount->entry->update(['status' => 'recount_done']);
        AuditLog::log('complete_recount', RecountRequest::class, $recount->id);

        return back()->with('success', 'Recount berhasil diselesaikan.');
    }

    // Get available teams/petugas for recount assignment
    public function getAssignOptions(Request $request)
    {
        $entryId = $request->get('entry_id');
        $entry = SoEntry::findOrFail($entryId);
        $session = $entry->session;

        // Get teams in the same session, exclude original team
        $teams = Team::where('session_id', $session->id)
            ->where('id', '!=', $entry->team_id)
            ->with('members.user')
            ->get();

        return response()->json(['teams' => $teams]);
    }
}
