<?php

namespace App\Http\Controllers;

use App\Models\SoSession;
use App\Models\Team;
use App\Models\SoEntry;
use App\Models\Location;
use App\Models\TeamLocationAllocation;
use Illuminate\Http\Request;

class TeamProgressController extends Controller
{
    public function index(Request $request)
    {
        // Get latest active or completed session
        $session = SoSession::whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$session) {
            return view('admin.monitoring.index', ['session' => null, 'teams' => collect()]);
        }

        // Get all teams in this session with their stats
        $teams = Team::where('session_id', $session->id)
            ->with(['leader', 'members.user', 'locationAllocations.location'])
            ->get()
            ->map(function ($team) use ($session) {
                $entries = SoEntry::where('team_id', $team->id)
                    ->where('session_id', $session->id)
                    ->get();

                $totalLocations = $team->locationAllocations->count();
                $totalEntries = $entries->count();
                $pendingEntries = $entries->where('status', 'pending')->count();
                $verifiedEntries = $entries->where('status', 'verified')->count();

                // Get unique locations that have entries AND are still allocated to this team
                $allocatedLocationIds = $team->locationAllocations->pluck('location_id');
                $locationsDone = $entries
                    ->whereIn('location_id', $allocatedLocationIds)
                    ->pluck('location_id')
                    ->unique()
                    ->count();

                // Get last entry time
                $lastEntry = $entries->sortByDesc('created_at')->first();

                // Per-petugas stats
                $petugasStats = $entries->groupBy('petugas_id')->map(function ($group) use ($team) {
                    $petugas = $team->members->firstWhere('user_id', $group->first()->petugas_id);
                    $user = $group->first()->petugas;
                    return [
                        'name' => $user ? ($user->full_name ?? $user->name) : 'Unknown',
                        'total' => $group->count(),
                        'pending' => $group->where('status', 'pending')->count(),
                        'verified' => $group->where('status', 'verified')->count(),
                    ];
                })->values();

                return [
                    'team' => $team,
                    'total_locations' => $totalLocations,
                    'locations_done' => $locationsDone,
                    'total_entries' => $totalEntries,
                    'pending' => $pendingEntries,
                    'verified' => $verifiedEntries,
                    'progress' => $totalLocations > 0 ? min(100, round(($locationsDone / $totalLocations) * 100)) : 0,
                    'last_entry_at' => $lastEntry?->created_at,
                    'petugas_stats' => $petugasStats,
                ];
            });

        // Overall stats — berbasis SEMUA lokasi master (termasuk yang tidak aktif)
        $allLocationIds = Location::pluck('id');
        $overallLocationCount = $allLocationIds->count();
        $overallDoneLocations = SoEntry::where('session_id', $session->id)
            ->whereIn('location_id', $allLocationIds)
            ->distinct()
            ->count('location_id');

        $overall = [
            'total_teams' => $teams->count(),
            'total_locations' => $overallLocationCount,
            'locations_done' => $overallDoneLocations,
            'total_entries' => SoEntry::where('session_id', $session->id)->count(),
            'pending' => SoEntry::where('session_id', $session->id)->where('status', 'pending')->count(),
            'verified' => SoEntry::where('session_id', $session->id)->where('status', 'verified')->count(),
            'progress' => $overallLocationCount > 0 ? min(100, round(($overallDoneLocations / $overallLocationCount) * 100)) : 0,
        ];

        return view('admin.monitoring.index', compact('session', 'teams', 'overall'));
    }
}
