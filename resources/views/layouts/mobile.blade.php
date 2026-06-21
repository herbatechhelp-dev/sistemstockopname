<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SISO' }} - Stock Opname</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        /* Mobile optimizations */
        * { -webkit-tap-highlight-color: transparent; }
        input, select, textarea, button { font-size: 16px; } /* Prevent zoom on iOS */
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 80px); }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">
    {{-- Mobile Header --}}
    <header class="sticky top-0 z-40 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold">SISO</h1>
                    <p class="text-xs text-blue-100 truncate max-w-[200px]">{{ auth()->user()->full_name ?? auth()->user()->name }}@if(auth()->user()->getActiveTeam()) - {{ auth()->user()->getActiveTeam()->name }}@endif</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div id="sync-indicator" class="w-2.5 h-2.5 rounded-full bg-green-400 shadow-sm animate-pulse"></div>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-blue-200 hover:text-white hover:bg-white/10 rounded-lg transition-all" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @if(isset($sessionName))
        <div class="px-4 py-2 bg-blue-800/80 backdrop-blur-sm text-xs flex items-center justify-between">
            <span class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ $sessionName }}</span>
            </span>
            <span class="flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}</span>
            </span>
        </div>
        @endif
    </header>

    {{-- Mobile Content --}}
    <main class="px-4 py-4 pb-28 safe-area-bottom">
        @if(session('success'))
            <x-toast type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-toast type="error" :message="session('error')" />
        @endif
        {{ $slot }}
    </main>

    {{-- Mobile Bottom Navigation --}}
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-lg z-40 pb-safe">
        <div class="flex justify-around py-2 px-1">
            @if(in_array(auth()->user()->role, ['petugas_so', 'team_leader']))
                <a href="/entry" class="flex flex-col items-center py-2 px-3 min-w-[64px] {{ request()->is('entry*') ? 'text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        @if(request()->is('entry*') && !request()->is('entry/create*'))
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-600 rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-xs mt-1 font-medium">Input</span>
                </a>
            @endif
            @if(auth()->user()->role === 'team_leader')
                <a href="/verification" class="flex flex-col items-center py-2 px-3 min-w-[64px] {{ request()->is('verification*') ? 'text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @if(request()->is('verification*'))
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-600 rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-xs mt-1 font-medium">Verifikasi</span>
                </a>
            @endif
        </div>
    </nav>
    @stack('scripts')
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 80px); }
    </style>
</body>
</html>
