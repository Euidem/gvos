@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — GVOS</title>

    {{-- Tailwind CSS CDN — Phase 0 staging only.
         Phase 1 will replace this with a compiled Vite build. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-50 font-sans antialiased">

<div class="min-h-screen flex">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col flex-shrink-0 min-h-screen">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-slate-700/60">
            <span class="text-xl font-bold tracking-tight text-white">GVOS</span>
            <span class="block text-xs text-slate-400 mt-0.5">Managed Operations</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-4 py-6 space-y-1">
            <a href="{{ url('/') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                My Profile
            </a>
        </nav>

        {{-- User footer --}}
        @auth
        <div class="px-4 py-4 border-t border-slate-700/60">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-xs font-semibold text-white flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left text-xs text-slate-400 hover:text-white transition-colors py-1">
                    Sign out
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- ── Main content ─────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-slate-800">{{ $title }}</h1>
            @auth
            <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium capitalize">
                {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
            </span>
            @endauth
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-8 py-8">
            {{ $slot }}
        </main>
    </div>

</div>

</body>
</html>
