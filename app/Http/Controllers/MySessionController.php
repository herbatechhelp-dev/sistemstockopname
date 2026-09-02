<?php

namespace App\Http\Controllers;

use App\Models\SoSession;
use App\Models\SoEntry;
use App\Models\Team;
use App\Models\TeamMember;
use App\Services\SessionContext;
use Illuminate\Http\Request;

class MySessionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sessions = SessionContext::allSessionsFor($user);

        // Optional filter by status
        $status = $request->get('status');
        if ($status && in_array($status, ['active','completed','closed','draft'])) {
            $sessions = $sessions->where('status', $status)->values();
        }

        // Enrich each session with team & stats for this user
        $items = $sessions->map(function (SoSession $session) use ($user) {
            // Find team of user in this session (as leader or member)
            if ($user->isTeamLeader()) {
                $team = Team::where('session_id', $session->id)
                    ->where('team_leader_id', $user->id)
                    ->first();
                // Fallback: also check membership (TL could be member in another session)
                if (!$team) {
                    $membership = TeamMember::where('user_id', $user->id)
                        ->whereHas('team', fn($q) => $q->where('session_id', $session->id))
                        ->first();
                    $team = $membership?->team;
                }
            } else {
                $membership = TeamMember::where('user_id', $user->id)
                    ->whereHas('team', fn($q) => $q->where('session_id', $session->id))
                    ->first();
                $team = $membership?->team;
            }

            $teamEntries = $team
                ? SoEntry::where('session_id', $session->id)->where('team_id', $team->id)->get()
                : collect();

            $myEntries = SoEntry::where('session_id', $session->id)
                ->where('petugas_id', $user->id)
                ->count();

            return [
                'session' => $session,
                'team' => $team,
                'total_entries_team' => $teamEntries->count(),
                'pending_team' => $teamEntries->where('status','pending')->count(),
                'verified_team' => $teamEntries->where('status','verified')->count(),
                'my_entries' => $myEntries,
                'allocations_count' => $team ? $team->locationAllocations()->count() : 0,
            ];
        });

        return view('my-sessions.index', [
            'items' => $items,
            'statusFilter' => $status,
        ]);
    }

    public function show(SoSession $session)
    {
        $user = auth()->user();

        // Ensure user has access to this session (was part of it)
        $allowed = SessionContext::allSessionsFor($user)->contains('id', $session->id);
        if (!$allowed && !$user->isAdminOrSuperadmin()) {
            abort(403, 'Anda tidak memiliki akses ke sesi ini.');
        }

        // Resolve user's team in this session
        if ($user->isTeamLeader()) {
            $team = Team::where('session_id', $session->id)
                ->where('team_leader_id', $user->id)
                ->first();
            if (!$team) {
                $membership = TeamMember::where('user_id', $user->id)
                    ->whereHas('team', fn($q) => $q->where('session_id', $session->id))
                    ->first();
                $team = $membership?->team;
            }
        } else {
            $membership = TeamMember::where('user_id', $user->id)
                ->whereHas('team', fn($q) => $q->where('session_id', $session->id))
                ->first();
            $team = $membership?->team;
        }

        $session->load(['teams.leader', 'teams.members.user', 'teams.locationAllocations.location']);

        // Entries for this session + user's team (read-only, no variance for field roles)
        $entries = $team
            ? SoEntry::where('session_id', $session->id)
                ->where('team_id', $team->id)
                ->with(['item.category', 'location', 'petugas'])
                ->orderBy('created_at','desc')
                ->get()
            : collect();

        // Overall stats team scoped
        $allocations = $team ? $team->locationAllocations : collect();
        $allocatedIds = $allocations->pluck('location_id');

        return view('my-sessions.show', compact('session', 'team', 'entries', 'allocations', 'allocatedIds'));
    }
}
