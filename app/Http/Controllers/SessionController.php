<?php

namespace App\Http\Controllers;

use App\Models\SoSession;
use App\Models\SessionSnapshot;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamLocationAllocation;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\SoEntry;
use App\Services\SessionContext;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $query = SoSession::with('creator')->withCount('entries', 'teams');
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        $sessions = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('sessions.index', compact('sessions'));
    }

    public function create()
    {
        $items = Item::where('is_active', true)->orderBy('sku')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        return view('sessions.create', compact('items', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $session = SoSession::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('create', SoSession::class, $session->id, null, $data);

        return redirect("/admin/sessions/{$session->id}")->with('success', 'Sesi SO berhasil dibuat.');
    }

    public function show(SoSession $session)
    {
        $session->load(['teams.leader', 'teams.members.user', 'teams.locationAllocations.location', 'entries']);
        $availableUsers = User::where('role', 'petugas_so')->where('is_active', true)->get();
        $teamLeaders = User::where('role', 'team_leader')->where('is_active', true)->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('sku')->get();
        return view('sessions.show', compact('session', 'availableUsers', 'teamLeaders', 'locations', 'items'));
    }

    // Start session - create snapshots
    public function start(SoSession $session)
    {
        if ($session->status !== 'draft') {
            return back()->with('error', 'Sesi hanya bisa dimulai dari status Draft.');
        }

        if ($session->teams()->count() === 0) {
            return back()->with('error', 'Minimal harus ada 1 tim sebelum sesi dimulai.');
        }

        // Create snapshots for all items at all allocated locations
        $allocations = TeamLocationAllocation::whereHas('team', function ($q) use ($session) {
            $q->where('session_id', $session->id);
        })->with('location')->get();

        $locationIds = $allocations->pluck('location_id')->unique();
        $items = Item::where('is_active', true)->get();

        // Snapshot = titik hitung nyata: hanya (item x lokasi) dengan stok sistem > 0.
        // Stok sistem di-generate deterministik per (item, lokasi) agar stabil antar sesi.
        $created = 0;
        foreach ($items as $item) {
            foreach ($locationIds as $locationId) {
                $qty = $this->simulatedStock($item->id, $locationId);
                if ($qty <= 0) {
                    continue; // item tidak tersedia di lokasi ini → bukan titik hitung
                }
                SessionSnapshot::create([
                    'session_id' => $session->id,
                    'item_id' => $item->id,
                    'location_id' => $locationId,
                    'system_qty' => $qty,
                ]);
                $created++;
            }
        }

        $session->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        AuditLog::log('start_session', SoSession::class, $session->id, null, ['snapshot_points' => $created]);

        return back()->with('success', "Sesi SO berhasil dimulai. {$created} titik hitung (item x lokasi dengan stok) telah dibuat.");
    }

    // Stok sistem simulasi yang deterministik per (item, lokasi):
    // kombinasi sama selalu menghasilkan stok sama, sebagian kombinasi bernilai 0
    // (item tidak tersimpan di lokasi itu) sehingga tidak menjadi titik hitung.
    private function simulatedStock(int $itemId, int $locationId): int
    {
        $seed = ($itemId * 73856093) ^ ($locationId * 19349663);
        mt_srand($seed);
        if (mt_rand(0, 99) < 25) {
            return 0;
        }
        return mt_rand(10, 500);
    }

    // Complete session
    public function complete(SoSession $session)
    {
        if ($session->status !== 'active') {
            return back()->with('error', 'Hanya sesi aktif yang dapat diselesaikan.');
        }

        $session->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        AuditLog::log('complete_session', SoSession::class, $session->id);

        return back()->with('success', 'Sesi SO berhasil diselesaikan.');
    }

    // Close session (final)
    public function close(SoSession $session)
    {
        if ($session->status !== 'completed') {
            return back()->with('error', 'Hanya sesi yang sudah diselesaikan yang dapat ditutup.');
        }

        $session->update(['status' => 'closed']);
        AuditLog::log('close_session', SoSession::class, $session->id);

        return back()->with('success', 'Sesi SO berhasil ditutup secara permanen.');
    }

    // Add team to session
    public function addTeam(Request $request, SoSession $session)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'team_leader_id' => 'required|exists:users,id',
        ]);

        $team = Team::create([
            'name' => $data['name'],
            'session_id' => $session->id,
            'team_leader_id' => $data['team_leader_id'],
        ]);

        AuditLog::log('create_team', Team::class, $team->id);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    // Add member to team
    public function addMember(Request $request, Team $team)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        TeamMember::firstOrCreate([
            'team_id' => $team->id,
            'user_id' => $data['user_id'],
        ]);

        return back()->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    // Allocate location to team
    public function allocateLocation(Request $request, Team $team)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        // Check collision: location already allocated to another team in this session
        $existing = TeamLocationAllocation::whereHas('team', function ($q) use ($team) {
            $q->where('session_id', $team->session_id);
        })->where('location_id', $data['location_id'])->first();

        if ($existing) {
            return back()->with('error', 'Lokasi sudah dialokasikan ke tim lain dalam sesi ini.');
        }

        TeamLocationAllocation::create([
            'team_id' => $team->id,
            'location_id' => $data['location_id'],
            'status' => 'assigned',
        ]);

        return back()->with('success', 'Lokasi berhasil dialokasikan ke tim.');
    }

    // Remove allocation
    public function removeAllocation(TeamLocationAllocation $allocation)
    {
        $hasEntries = SoEntry::where('session_id', $allocation->team->session_id)
            ->where('location_id', $allocation->location_id)
            ->exists();

        if ($hasEntries) {
            return back()->with('error', 'Alokasi lokasi tidak dapat dihapus karena sudah memiliki data entri.');
        }

        $allocation->delete();
        return back()->with('success', 'Alokasi lokasi berhasil dihapus.');
    }

    // Remove member
    public function removeMember(TeamMember $member)
    {
        $member->delete();
        return back()->with('success', 'Anggota tim berhasil dihapus.');
    }

    // Delete team
    public function deleteTeam(Team $team)
    {
        if ($team->entries()->count() > 0) {
            return back()->with('error', 'Tim tidak dapat dihapus karena sudah memiliki data entri.');
        }
        $team->delete();
        return back()->with('success', 'Tim berhasil dihapus.');
    }

    // ===== Multi-session context (Petugas & TL) =====

    public function showPicker(Request $request)
    {
        $user = auth()->user();
        $sessions = SessionContext::activeSessionsFor($user);

        if ($sessions->isEmpty()) {
            return redirect('/entry')->withErrors(['session' => 'Tidak ada sesi Stock Opname yang aktif untuk akun Anda.']);
        }

        // Ambil informasi tim user di tiap sesi untuk tampilan
        $items = $sessions->map(function ($session) use ($user) {
            if ($user->isTeamLeader()) {
                $team = \App\Models\Team::where('session_id', $session->id)
                    ->where('team_leader_id', $user->id)
                    ->first();
            } else {
                $membership = \App\Models\TeamMember::where('user_id', $user->id)
                    ->whereHas('team', fn($q) => $q->where('session_id', $session->id))
                    ->first();
                $team = $membership?->team;
            }

            return [
                'session' => $session,
                'team' => $team,
            ];
        });

        return view('session-picker', [
            'items' => $items,
            'redirect' => $request->get('redirect', '/entry'),
        ]);
    }

    public function selectSession(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|exists:so_sessions,id',
            'redirect' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $sessions = SessionContext::activeSessionsFor($user);

        if (!$sessions->contains('id', (int) $data['session_id'])) {
            return back()->with('error', 'Anda tidak memiliki akses ke sesi tersebut.');
        }

        SessionContext::set((int) $data['session_id']);

        $redirect = $data['redirect'] ?? '/entry';
        $allowedPrefixes = ['entry', 'verification', 'session'];
        $path = ltrim($redirect, '/');
        $safe = in_array(explode('/', $path)[0], $allowedPrefixes, true) ? '/' . $path : '/entry';

        return redirect($safe)->with('success', 'Sesi SO berhasil dipilih.');
    }

    public function clearSession()
    {
        SessionContext::clear();
        return redirect('/entry');
    }
}
