<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SISO' }} - Stock Opname</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        * {
            -webkit-tap-highlight-color: transparent;
        }
        input, select, textarea, button {
            font-size: 16px;
        }
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 88px);
        }
    </style>
</head>
<body class="text-gray-900 min-h-screen pb-safe">
    @php
        use App\Services\SessionContext;
        $mobileUser = auth()->user();
        $mobileSession = SessionContext::resolve($mobileUser);
        $mobileTeam = $mobileSession ? $mobileUser->getActiveTeam($mobileSession->id) : null;
    @endphp
    {{-- Mobile Header --}}
    <header class="sticky top-0 z-40 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 border-b border-slate-700/50 shadow-md">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-blue-600/20 border border-blue-500/30 rounded-xl flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-wider">SISO HBT</h1>
                    <p class="text-[10px] text-slate-400 font-medium">
                        {{ $mobileUser->full_name ?? $mobileUser->name }}
                        @if($mobileTeam)
                            <span class="text-blue-400 font-bold"> • {{ $mobileTeam->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                {{-- Sync status --}}
                <div class="flex items-center space-x-1 bg-slate-800 border border-slate-700 px-2 py-1 rounded-full text-[10px]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-emerald-400 font-semibold uppercase tracking-wider">Online</span>
                </div>
                
                {{-- Ganti sesi --}}
                <a href="{{ route('session.picker') }}" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-xl transition-all duration-200" title="Ganti Sesi">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </a>
                {{-- Logout button --}}
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all duration-200" title="Logout">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @if(isset($sessionName) ? $sessionName : ($mobileSession ? $mobileSession->name : null))
        <div class="px-4 py-2 bg-slate-900 border-t border-slate-800/80 text-[10px] text-slate-400 flex items-center justify-between">
            <span class="flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="font-semibold text-slate-300">{{ isset($sessionName) ? $sessionName : ($mobileSession->name ?? '') }}</span>
            </span>
            <span class="flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}</span>
            </span>
        </div>
        @endif
    </header>

    {{-- Mobile Content --}}
    <main class="px-4 py-4 pb-28">
        @if(session('success'))
            <x-toast type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-toast type="error" :message="session('error')" />
        @endif
        {{ $slot }}
    </main>

    {{-- Mobile Bottom Navigation --}}
    <div class="fixed bottom-4 left-4 right-4 bg-slate-900/95 backdrop-blur-lg border border-slate-700/50 rounded-2xl shadow-xl shadow-slate-900/40 z-40 overflow-hidden">
        <div class="flex justify-around py-3 px-2">
            @if(in_array(auth()->user()->role, ['petugas_so', 'team_leader']))
                <a href="/entry" class="flex flex-col items-center py-1.5 px-4 rounded-xl transition-all duration-200 {{ request()->is('entry*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 scale-105' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="text-[10px] mt-1 font-semibold tracking-wide">SO Input</span>
                </a>
            @endif
            @if(auth()->user()->role === 'team_leader')
                <a href="/verification" class="flex flex-col items-center py-1.5 px-4 rounded-xl transition-all duration-200 {{ request()->is('verification*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 scale-105' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] mt-1 font-semibold tracking-wide">Verifikasi</span>
                </a>
            @endif
        </div>
    </div>
    @stack('scripts')
</body>
</html>
