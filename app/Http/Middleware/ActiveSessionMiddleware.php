<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SoSession;

class ActiveSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $activeSession = SoSession::where('status', 'active')->first();

        if (!$activeSession) {
            return redirect('/entry')->withErrors(['session' => 'Tidak ada sesi Stock Opname yang aktif.']);
        }

        return $next($request);
    }
}
