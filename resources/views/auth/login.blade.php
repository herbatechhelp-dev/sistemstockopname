<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - SISO Stock Opname</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 min-h-screen flex items-center justify-center p-4 overflow-x-hidden relative">
    {{-- Background decorations --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10 animate-fade-up">
        {{-- Logo & Branding --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/15 border border-blue-500/30 rounded-2xl mb-4 shadow-inner backdrop-blur-md">
                <svg class="w-8 h-8 text-blue-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-wider">SISO HBT</h1>
            <p class="text-slate-400 mt-1.5 text-xs font-semibold uppercase tracking-widest">Stock Opname System</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-950/50 p-8 border border-slate-100">
            <h2 class="text-xl font-extrabold text-slate-800 mb-1.5">Selamat Datang</h2>
            <p class="text-xs text-slate-400 font-semibold mb-6">Silakan masuk ke akun Anda</p>

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-start space-x-3">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-bold leading-relaxed">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-600 mb-2 uppercase tracking-wide">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            placeholder="Ketik email Anda..."
                            class="w-full pl-11 pr-4 py-3.5 border border-slate-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition text-sm text-slate-800 placeholder-slate-400 font-medium">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-600 mb-2 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3.5 border border-slate-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition text-sm text-slate-800 placeholder-slate-400 font-medium">
                    </div>
                </div>
                <button type="submit" class="w-full py-4 px-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 hover:shadow-indigo-600/20 active:scale-[0.98] text-white font-extrabold rounded-2xl shadow-lg transition-all text-sm uppercase tracking-wider mt-2">
                    Masuk
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-slate-500/80 text-[10px] font-bold uppercase tracking-wider mt-8">&copy; {{ date('Y') }} PT. HBT Indonesia &middot; SISO v1.2</p>
    </div>

    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</body>
</html>
