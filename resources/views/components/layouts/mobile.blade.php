<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SISO' }} - Stock Opname</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    {{-- Mobile Header --}}
    <header class="sticky top-0 z-40 bg-blue-700 text-white shadow-lg">
        <div class="px-4 py-3 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold">SISO</h1>
                <p class="text-xs text-blue-200">{{ auth()->user()->full_name ?? auth()->user()->name }}@if(auth()->user()->getActiveTeam()) - {{ auth()->user()->getActiveTeam()->name }}@endif</p>
            </div>
            <div class="flex items-center space-x-3">
                <div id="sync-indicator" class="w-2 h-2 rounded-full bg-green-400"></div>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-blue-200 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @if(isset($sessionName) && $sessionName)
        <div class="px-4 py-2 bg-blue-800 text-xs text-blue-100 flex items-center justify-between">
            <span>Sesi: {{ $sessionName }}</span>
            <span>{{ now()->format('H:i') }}</span>
        </div>
        @endif
    </header>

    {{-- Mobile Content --}}
    <main class="px-4 py-4 pb-24">
        @if(session('success'))
            <x-toast type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-toast type="error" :message="session('error')" />
        @endif
        {{ $slot }}
    </main>

    {{-- Mobile Bottom Navigation --}}
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
        <div class="flex justify-around py-2">
            @if(in_array(auth()->user()->role, ['petugas_so', 'team_leader']))
                <a href="/entry" class="flex flex-col items-center py-2 px-3 {{ request()->is('entry*') ? 'text-blue-700' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="text-xs mt-1">Input</span>
                </a>
            @endif
            @if(auth()->user()->role === 'team_leader')
                <a href="/verification" class="flex flex-col items-center py-2 px-3 {{ request()->is('verification*') ? 'text-blue-700' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs mt-1">Verifikasi</span>
                </a>
            @endif
        </div>
    </nav>
    @stack('scripts')
</body>
</html>
