<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SoSession;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $query = SoSession::with('creator')->withCount('entries', 'teams');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        $sessions = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('superadmin.sessions.index', compact('sessions'));
    }

    public function show(SoSession $session)
    {
        $session->load(['teams.leader', 'teams.members.user', 'teams.locationAllocations.location', 'entries']);
        return view('superadmin.sessions.show', compact('session'));
    }

    // Superadmin can force close any session
    public function forceClose(SoSession $session)
    {
        $session->update(['status' => 'closed', 'ended_at' => now()]);
        AuditLog::log('superadmin_force_close', SoSession::class, $session->id);
        return back()->with('success', 'Sesi berhasil ditutup paksa.');
    }
}
