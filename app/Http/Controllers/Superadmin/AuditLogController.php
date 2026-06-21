<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%");
        }
        if ($user = $request->get('user_id')) {
            $query->where('user_id', $user);
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('superadmin.audit-logs.index', compact('logs', 'users', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('superadmin.audit-logs.show', compact('auditLog'));
    }
}
