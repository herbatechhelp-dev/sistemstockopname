<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SoSession;
use App\Models\User;
use App\Models\SoEntry;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_tl' => User::where('role', 'team_leader')->count(),
            'total_petugas' => User::where('role', 'petugas_so')->count(),
            'active_sessions' => SoSession::where('status', 'active')->count(),
            'completed_sessions' => SoSession::where('status', 'completed')->count(),
            'closed_sessions' => SoSession::where('status', 'closed')->count(),
            'total_entries' => SoEntry::count(),
        ];

        $recentSessions = SoSession::with('creator')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(10)->get();

        return view('superadmin.dashboard', compact('stats', 'recentSessions', 'recentLogs'));
    }
}
