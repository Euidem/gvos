@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — GVOS</title>

    {{-- Tailwind CSS CDN — staging only. Replace with compiled Vite build before production. --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "on-surface-variant":        "#45464d",
                        "secondary-fixed":           "#d8e2ff",
                        "secondary-fixed-dim":       "#adc6ff",
                        "sidebar-bg":                "#0B0F19",
                        "surface-container-low":     "#f2f4f6",
                        "surface-container-lowest":  "#ffffff",
                        "surface-container-high":    "#e6e8ea",
                        "surface-container":         "#eceef0",
                        "status-completed":  "#059669",
                        "status-active":     "#10B981",
                        "status-trial":      "#8B5CF6",
                        "status-payment-due":"#F59E0B",
                        "status-suspended":  "#64748B",
                        "status-blocked":    "#EF4444",
                        "status-urgent":     "#B91C1C",
                        "on-background":     "#191c1e",
                        "on-surface":        "#191c1e",
                        "on-primary":        "#ffffff",
                        "on-primary-container": "#7c839b",
                        "primary-container":    "#131b2e",
                        "secondary-container":  "#2170e4",
                        "on-secondary-container":"#fefcff",
                        "border-subtle":     "#E2E8F0",
                        "outline":           "#76777d",
                        "outline-variant":   "#c6c6cd",
                        "secondary":         "#0058be",
                        "on-secondary":      "#ffffff",
                        "surface":           "#f7f9fb",
                        "background":        "#f7f9fb",
                        "error":             "#ba1a1a",
                        "primary":           "#000000"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg":      "0.5rem",
                        "xl":      "0.75rem",
                        "full":    "9999px"
                    },
                    spacing: {
                        "gutter":            "24px",
                        "input-gap":         "16px",
                        "container-margin":  "32px",
                        "card-padding":      "24px",
                        "section-gap":       "40px"
                    },
                    fontFamily: {
                        "headline-lg":        ["Manrope", "sans-serif"],
                        "headline-lg-mobile": ["Manrope", "sans-serif"],
                        "headline-md":        ["Manrope", "sans-serif"],
                        "display-lg":         ["Manrope", "sans-serif"],
                        "label-md":           ["Inter", "sans-serif"],
                        "body-lg":            ["Inter", "sans-serif"],
                        "body-md":            ["Inter", "sans-serif"],
                        "body-sm":            ["Inter", "sans-serif"],
                        "mono-sm":            ["JetBrains Mono", "monospace"],
                        "sans":               ["Inter", "ui-sans-serif", "system-ui"]
                    },
                    fontSize: {
                        "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "700", "letterSpacing": "-0.01em"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-lg":     ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "body-md":     ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "body-sm":     ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-md":    ["12px", {"lineHeight": "16px", "fontWeight": "600", "letterSpacing": "0.02em"}],
                        "mono-sm":     ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "display-lg":  ["48px", {"lineHeight": "56px", "fontWeight": "800", "letterSpacing": "-0.02em"}]
                    },
                    boxShadow: {
                        "card": "0px 4px 20px rgba(0,0,0,0.04)",
                        "subtle": "0px 4px 20px rgba(0,0,0,0.04)"
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts: Manrope (headlines) · Inter (body) · JetBrains Mono (mono) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    {{-- Material Symbols Outlined icon font --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        /* Stitch card shadow pattern */
        .card-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0px 8px 24px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="h-full bg-background font-sans antialiased">

{{-- Hidden div: ensure Tailwind CDN generates all dynamic nav classes --}}
<div class="hidden bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed text-on-primary-container hover:text-secondary-fixed hover:bg-white/5 hover:brightness-110 shadow-card shadow-subtle bg-secondary text-on-secondary bg-surface-container-lowest border-border-subtle"></div>

<div class="min-h-screen flex">

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <aside class="w-[280px] bg-sidebar-bg text-white flex flex-col flex-shrink-0 min-h-screen">

        {{-- Logo --}}
        <div class="px-6 pt-6 pb-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-on-secondary" style="font-variation-settings: 'FILL' 1; font-size: 20px;">hub</span>
                </div>
                <div>
                    <h1 class="font-bold text-secondary-fixed text-sm leading-tight tracking-tight">GVOS Platform</h1>
                    <p class="text-[10px] text-on-primary-container mt-0.5 uppercase tracking-wider">Enterprise Ops</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        @php
            $dashboardActive = request()->is('/') || request()->is('*/dashboard');
            $workspaceActive = request()->routeIs('workspace.*');
            $profileActive   = request()->routeIs('profile.*');
        @endphp

        <nav class="flex-1 px-4 py-6 space-y-1">

            <a href="{{ url('/') }}"
               class="{{ $dashboardActive
                    ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed font-bold'
                    : 'text-on-primary-container hover:text-secondary-fixed hover:bg-white/5' }}
                  flex items-center gap-3 px-3 py-3 rounded-lg transition-colors">
                <span class="material-symbols-outlined flex-shrink-0" style="font-size: 20px;">dashboard</span>
                <span class="text-xs font-semibold tracking-wide">Dashboard</span>
            </a>

            <a href="{{ route('workspace.index') }}"
               class="{{ $workspaceActive
                    ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed font-bold'
                    : 'text-on-primary-container hover:text-secondary-fixed hover:bg-white/5' }}
                  flex items-center gap-3 px-3 py-3 rounded-lg transition-colors">
                <span class="material-symbols-outlined flex-shrink-0" style="font-size: 20px;">workspaces</span>
                <span class="text-xs font-semibold tracking-wide">Workspaces</span>
            </a>

            <a href="{{ route('profile.show') }}"
               class="{{ $profileActive
                    ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed font-bold'
                    : 'text-on-primary-container hover:text-secondary-fixed hover:bg-white/5' }}
                  flex items-center gap-3 px-3 py-3 rounded-lg transition-colors">
                <span class="material-symbols-outlined flex-shrink-0" style="font-size: 20px;">person</span>
                <span class="text-xs font-semibold tracking-wide">My Profile</span>
            </a>

        </nav>

        {{-- User footer --}}
        @auth
        <div class="px-4 pt-4 pb-6 border-t border-white/10">
            <div class="flex items-center gap-3 px-3 py-4 bg-white/5 rounded-xl mb-3">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary text-sm font-bold flex-shrink-0 border border-secondary-fixed/30">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-on-primary-container truncate uppercase tracking-wider mt-0.5">
                        {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left text-xs text-on-primary-container hover:text-white transition-colors py-2 px-3 flex items-center gap-2 rounded-lg hover:bg-white/5">
                    <span class="material-symbols-outlined flex-shrink-0" style="font-size: 16px;">logout</span>
                    Sign out
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- ── Main content ──────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="sticky top-0 h-16 bg-surface-container-lowest border-b border-border-subtle shadow-sm px-8 flex items-center justify-between z-40">
            {{-- Page title --}}
            <h1 class="text-sm font-semibold text-on-surface">{{ $title }}</h1>

            @auth
            <div class="flex items-center gap-3">
                {{-- Notification bell --}}
                <button type="button"
                        class="p-2 text-outline hover:bg-surface-container-low rounded-full transition-all"
                        title="Notifications">
                    <span class="material-symbols-outlined" style="font-size: 20px;">notifications</span>
                </button>
                {{-- Security indicator --}}
                <button type="button"
                        class="p-2 text-outline hover:bg-surface-container-low rounded-full transition-all"
                        title="Security">
                    <span class="material-symbols-outlined" style="font-size: 20px;">security</span>
                </button>
                {{-- Vertical divider --}}
                <div class="h-8 w-px bg-border-subtle mx-1"></div>
                {{-- User avatar + info --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-on-secondary text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-xs font-semibold text-on-surface leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-outline uppercase tracking-wide leading-tight mt-0.5">
                            {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                        </p>
                    </div>
                </div>
            </div>
            @endauth
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-8 bg-background">
            {{ $slot }}
        </main>
    </div>

</div>

</body>
</html>
