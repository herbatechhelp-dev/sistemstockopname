<?php

namespace App\Http\Controllers;

use App\Models\SoEntry;
use App\Models\SoSession;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamLocationAllocation;
use App\Models\Item;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeSession = SoSession::where('status', 'active')->first();

        if (!$activeSession) {
            return view('entry.index', ['session' => null, 'entries' => collect()]);
        }

        // Get user's team in active session
        $team = $this->getUserTeam($user, $activeSession);
        if (!$team) {
            return view('entry.index', ['session' => $activeSession, 'team' => null, 'entries' => collect()]);
        }

        $entries = SoEntry::where('team_id', $team->id)
            ->where('session_id', $activeSession->id)
            ->with(['item', 'location', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('entry.index', [
            'session' => $activeSession,
            'team' => $team,
            'entries' => $entries,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $activeSession = SoSession::where('status', 'active')->first();

        if (!$activeSession) {
            return redirect('/entry')->with('error', 'Tidak ada sesi SO yang aktif.');
        }

        $team = $this->getUserTeam($user, $activeSession);
        if (!$team) {
            return redirect('/entry')->with('error', 'Anda belum dialokasikan ke tim manapun.');
        }

        // Get locations allocated to user's team
        $locations = TeamLocationAllocation::where('team_id', $team->id)
            ->with('location')
            ->get()
            ->pluck('location');

        return view('entry.create', [
            'session' => $activeSession,
            'team' => $team,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'item_id' => 'required|exists:items,id',
            'batch_code' => 'required|string|max:100',
            'fisik_qty' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Validate: if qty is 0, keterangan is mandatory
        if ($data['fisik_qty'] == 0 && empty($data['keterangan'])) {
            return back()->withErrors(['keterangan' => 'Keterangan wajib diisi jika kuantitas fisik bernilai 0.'])->withInput();
        }

        $user = auth()->user();
        $activeSession = SoSession::where('status', 'active')->firstOrFail();
        $team = $this->getUserTeam($user, $activeSession);

        if (!$team) {
            return back()->with('error', 'Anda belum dialokasikan ke tim manapun.');
        }

        // Verify location belongs to team's allocation
        $allocated = TeamLocationAllocation::where('team_id', $team->id)
            ->where('location_id', $data['location_id'])
            ->exists();

        if (!$allocated) {
            return back()->with('error', 'Lokasi tidak dialokasikan ke tim Anda.');
        }

        // Get item UoM
        $item = Item::with('uom')->findOrFail($data['item_id']);

        $entry = SoEntry::create([
            'session_id' => $activeSession->id,
            'team_id' => $team->id,
            'petugas_id' => $user->id,
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'uom' => $item->uom->abbreviation,
            'batch_code' => $data['batch_code'] ?? null,
            'fisik_qty' => $data['fisik_qty'],
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        AuditLog::log('create_entry', SoEntry::class, $entry->id, null, [
            'item' => $item->name,
            'location' => $data['location_id'],
            'fisik_qty' => $data['fisik_qty'],
        ]);

        return redirect('/entry')->with('success', 'Data hitungan berhasil disimpan.');
    }

    private function getUserTeam($user, SoSession $session): ?Team
    {
        // If user is TL, find team where they are leader
        if ($user->role === 'team_leader') {
            return Team::where('session_id', $session->id)
                ->where('team_leader_id', $user->id)
                ->first();
        }

        // If user is petugas, find team via membership
        $membership = TeamMember::where('user_id', $user->id)
            ->whereHas('team', function ($q) use ($session) {
                $q->where('session_id', $session->id);
            })->first();

        return $membership?->team;
    }

    // API: Get item by SKU (for barcode scan)
    public function getItemBySku(Request $request)
    {
        $sku = $request->get('sku', '');
        $item = Item::with(['category', 'uom'])
            ->where('sku', $sku)
            ->where('is_active', true)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item tidak ditemukan.'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'category' => $item->category->name,
            'category_code' => $item->category->code,
            'uom' => $item->uom->abbreviation,
        ]);
    }
}
