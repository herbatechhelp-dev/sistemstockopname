<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SessionContext;

class ActiveSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Admin/superadmin tidak lewat alur ini (dashboard punya dropdown sendiri)
        if ($user->isAdminOrSuperadmin()) {
            return $next($request);
        }

        $sessions = SessionContext::activeSessionsFor($user);

        if ($sessions->isEmpty()) {
            return redirect('/entry')->withErrors(['session' => 'Tidak ada sesi Stock Opname yang aktif untuk akun Anda.']);
        }

        $selected = SessionContext::resolve($user);

        if (!$selected) {
            return redirect()->route('session.picker', ['redirect' => $request->path()]);
        }

        SessionContext::set($selected->id);

        return $next($request);
    }
}
