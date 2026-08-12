<?php

namespace App\Http\Controllers;

use App\Models\SoSession;
use App\Models\SoEntry;
use App\Models\SessionSnapshot;
use App\Models\Item;
use App\Models\Location;
use App\Models\Category;
use App\Services\SessionContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $session = SessionContext::adminSelected(
            $request->has('session_id') ? (int) $request->get('session_id') : null
        );

        if (!$session) {
            return view('dashboard.index', [
                'session' => null,
                'stats' => [],
                'entries' => collect(),
                'categories' => collect(),
                'locations' => collect(),
                'allSessions' => collect(),
            ]);
        }

        // Get stats
        $totalSnapshots = SessionSnapshot::where('session_id', $session->id)->count();
        $totalEntries = SoEntry::where('session_id', $session->id)->count();
        $verifiedEntries = SoEntry::where('session_id', $session->id)->where('status', 'verified')->count();
        $pendingEntries = SoEntry::where('session_id', $session->id)->where('status', 'pending')->count();

        // Calculate variance stats
        $entries = SoEntry::where('session_id', $session->id)
            ->with(['item.category', 'location'])
            ->get();

        $matchCount = 0;
        $minusCount = 0;
        $plusCount = 0;

        foreach ($entries as $entry) {
            $variance = $entry->variance;
            if ($variance === null) continue;
            if ($variance == 0) $matchCount++;
            elseif ($variance < 0) $minusCount++;
            else $plusCount++;
        }

        $stats = [
            'total_snapshots' => $totalSnapshots,
            'total_entries' => $totalEntries,
            'verified_entries' => $verifiedEntries,
            'pending_entries' => $pendingEntries,
            'progress' => $totalSnapshots > 0 ? round(($totalEntries / $totalSnapshots) * 100) : 0,
            'match' => $matchCount,
            'minus' => $minusCount,
            'plus' => $plusCount,
        ];

        // Filter entries
        $query = SoEntry::where('session_id', $session->id)
            ->with(['item.category', 'location', 'petugas']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhereHas('location', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhere('batch_code', 'like', "%{$search}%");
            });
        }
        if ($category = $request->get('category_id')) {
            $query->whereHas('item', fn($q) => $q->where('category_id', $category));
        }
        if ($location = $request->get('location_id')) {
            $query->where('location_id', $location);
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $allSessions = SessionContext::adminOptions();

        return view('dashboard.index', compact('session', 'stats', 'entries', 'categories', 'locations', 'allSessions'));
    }

    public function show(SoEntry $entry)
    {
        $entry->load(['item.category', 'location', 'petugas', 'revisions.changer', 'recountRequests']);
        $snapshot = $entry->getSnapshot();
        return view('dashboard.show', compact('entry', 'snapshot'));
    }
}
