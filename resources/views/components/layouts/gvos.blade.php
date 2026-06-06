@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — GVOS</title>

    {{-- Tailwind CSS CDN — staging only. Replace with compiled Vite build before production. --}}
    {{-- CRITICAL: tailwind.config MUST be defined BEFORE the CDN <script> loads. --}}
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
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

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
        /* ── GVOS Design Token CSS Fallback ────────────────────────────────────
           Ensures GVOS custom Tailwind tokens render even if the CDN JIT misses
           them. These rules are intentional and permanent. Do NOT remove.
           ──────────────────────────────────────────────────────────────────── */
        .bg-sidebar-bg{background-color:#0B0F19}
        .text-secondary-fixed{color:#d8e2ff}
        .text-secondary-fixed-dim{color:#adc6ff}
        .text-on-primary-container{color:#7c839b}
        .bg-secondary-container{background-color:#2170e4}
        .text-on-secondary-container{color:#fefcff}
        .bg-secondary{background-color:#0058be}
        .text-secondary{color:#0058be}
        .border-secondary{border-color:#0058be}
        .border-secondary-fixed{border-color:#d8e2ff}
        .text-on-secondary{color:#ffffff}
        .bg-surface,.bg-background{background-color:#f7f9fb}
        .bg-surface-container{background-color:#eceef0}
        .bg-surface-container-low{background-color:#f2f4f6}
        .bg-surface-container-lowest{background-color:#ffffff}
        .bg-primary-container{background-color:#131b2e}
        .text-on-surface{color:#191c1e}
        .text-on-surface-variant{color:#45464d}
        .text-outline{color:#76777d}
        .text-outline-variant{color:#c6c6cd}
        .border-border-subtle{border-color:#E2E8F0}
        .shadow-card{box-shadow:0px 4px 20px rgba(0,0,0,.04)}
        .shadow-subtle{box-shadow:0px 4px 20px rgba(0,0,0,.04)}
        .bg-status-active{background-color:#10B981}.text-status-active{color:#10B981}.border-status-active{border-color:#10B981}
        .bg-status-completed{background-color:#059669}.text-status-completed{color:#059669}.border-status-completed{border-color:#059669}
        .bg-status-payment-due{background-color:#F59E0B}.text-status-payment-due{color:#F59E0B}.border-status-payment-due{border-color:#F59E0B}
        .bg-status-blocked{background-color:#EF4444}.text-status-blocked{color:#EF4444}.border-status-blocked{border-color:#EF4444}
        .bg-status-trial{background-color:#8B5CF6}.text-status-trial{color:#8B5CF6}.border-status-trial{border-color:#8B5CF6}
        .bg-status-suspended{background-color:#64748B}.text-status-suspended{color:#64748B}
        .bg-status-urgent{background-color:#B91C1C}.text-status-urgent{color:#B91C1C}
        .bg-secondary\/5{background-color:rgba(0,88,190,.05)}
        .bg-secondary\/10{background-color:rgba(0,88,190,.1)}
        .bg-secondary\/20{background-color:rgba(0,88,190,.2)}
        .border-secondary\/20{border-color:rgba(0,88,190,.2)}
        .border-secondary\/30{border-color:rgba(0,88,190,.3)}
        .text-secondary\/80{color:rgba(0,88,190,.8)}
        .bg-white\/5{background-color:rgba(255,255,255,.05)}
        .bg-white\/10{background-color:rgba(255,255,255,.1)}
        .bg-white\/15{background-color:rgba(255,255,255,.15)}
        .bg-white\/20{background-color:rgba(255,255,255,.2)}
        .border-white\/10{border-color:rgba(255,255,255,.1)}
        .border-white\/15{border-color:rgba(255,255,255,.15)}
        .bg-status-active\/10{background-color:rgba(16,185,129,.1)}
        .border-status-active\/20{border-color:rgba(16,185,129,.2)}
        .bg-status-completed\/10{background-color:rgba(5,150,105,.1)}
        .border-status-completed\/20{border-color:rgba(5,150,105,.2)}
        .bg-status-payment-due\/10{background-color:rgba(245,158,11,.1)}
        .border-status-payment-due\/20{border-color:rgba(245,158,11,.2)}
        .bg-status-blocked\/10{background-color:rgba(239,68,68,.1)}
        .border-status-blocked\/20{border-color:rgba(239,68,68,.2)}
        .bg-status-trial\/10{background-color:rgba(139,92,246,.1)}
        .border-status-trial\/20{border-color:rgba(139,92,246,.2)}
        .bg-status-suspended\/10{background-color:rgba(100,116,139,.1)}
        .text-secondary-fixed\/70{color:rgba(216,226,255,.7)}
        .border-secondary-fixed\/30{border-color:rgba(216,226,255,.3)}
        .border-secondary-fixed\/40{border-color:rgba(216,226,255,.4)}
        .focus\:ring-secondary\/20:focus{box-shadow:0 0 0 2px rgba(0,88,190,.2)}
        .focus\:border-secondary:focus{border-color:#0058be}
        .hover\:brightness-110:hover{filter:brightness(1.1)}
        .active\:scale-\[0\.98\]:active{transform:scale(.98)}
        .active\:scale-95:active{transform:scale(.95)}
        .hover\:bg-secondary\/10:hover{background-color:rgba(0,88,190,.1)}
    </style>
</head>
<body class="bg-background text-on-surface font-body-md h-full" style="background-color:#f7f9fb">
<!-- GVOS UI Visual Repair v3 active -->

{{-- Hidden div: force Tailwind CDN to generate all dynamic classes used in nav active/inactive states --}}
<div class="hidden
    bg-white/10 border-l-4 border-secondary-fixed
    text-secondary-fixed text-secondary-fixed-dim
    text-on-primary-container text-on-surface-variant
    hover:text-secondary-fixed hover:bg-white/5
    hover:brightness-110 active:scale-95
    shadow-card shadow-subtle
    bg-secondary text-on-secondary
    bg-surface-container-lowest border-border-subtle
    font-label-md text-label-md font-headline-md text-headline-md
    font-body-md text-body-md font-body-sm text-body-sm
    transition-transform transition-colors"></div>

<div class="min-h-screen flex">

    {{-- ── Sidebar (Stitch: w-[280px] fixed dark sidebar) ─────────────────── --}}
    {{-- Note: flex-shrink-0 keeps 280px column without fixed positioning.    --}}
    {{-- Visual Repair v3: inline style is the structural fallback for #0B0F19 --}}
    <aside class="w-[280px] text-white flex flex-col flex-shrink-0 min-h-screen py-gutter px-4 z-50"
           style="background-color:#0B0F19">

        {{-- ── Logo (Stitch: hub icon + GVOS Platform + Enterprise Ops, no border below) --}}
        <div class="mb-8 px-2 flex items-center gap-3">
            <div class="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-on-secondary"
                      style="font-variation-settings: 'FILL' 1; font-size: 20px;">hub</span>
            </div>
            <div>
                <h1 class="font-headline-md text-headline-md font-bold text-secondary-fixed leading-none">GVOS Platform</h1>
                <p class="font-label-md text-label-md text-on-primary-container">Enterprise Ops</p>
            </div>
        </div>

        {{-- ── Navigation ───────────────────────────────────────────────────── --}}
        @php
            $dashboardActive = request()->is('/') || request()->is('*/dashboard');
            $workspaceActive = request()->routeIs('workspace.*');
            $profileActive   = request()->routeIs('profile.*');
        @endphp

        {{-- Stitch: active = bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold active:scale-95 --}}
        {{-- Stitch: inactive = text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5 transition-colors --}}
        <nav class="flex-1 space-y-1">

            <a href="{{ url('/') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-lg transition-colors
                      {{ $dashboardActive
                           ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold active:scale-95'
                           : 'text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5' }}">
                <span class="material-symbols-outlined" style="font-size: 20px;">dashboard</span>
                <span class="font-label-md text-label-md">Dashboard</span>
            </a>

            <a href="{{ route('workspace.index') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-lg transition-colors
                      {{ $workspaceActive
                           ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold active:scale-95'
                           : 'text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5' }}">
                <span class="material-symbols-outlined" style="font-size: 20px;">workspaces</span>
                <span class="font-label-md text-label-md">Workspaces</span>
            </a>

            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-lg transition-colors
                      {{ $profileActive
                           ? 'bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold active:scale-95'
                           : 'text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5' }}">
                <span class="material-symbols-outlined" style="font-size: 20px;">person</span>
                <span class="font-label-md text-label-md">My Profile</span>
            </a>

        </nav>

        {{-- ── Sidebar footer (Stitch: border-t → Quick Action → Settings → Support → Profile card) --}}
        @auth
        <div class="mt-auto pt-gutter border-t border-white/10 space-y-1">

            {{-- Quick Action button (Stitch: bg-secondary rounded-xl with add icon) --}}
            {{-- Routes to workspace index as the primary action entry point --}}
            <a href="{{ route('workspace.index') }}"
               class="w-full flex items-center justify-center gap-2 bg-secondary text-on-secondary py-3 rounded-xl font-label-md text-label-md mb-5 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Quick Action
            </a>

            {{-- Settings → Profile in GVOS context --}}
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-lg text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5 transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">settings</span>
                <span class="font-label-md text-label-md">Settings</span>
            </a>

            {{-- Support (placeholder — no support portal in scope yet) --}}
            <div class="flex items-center gap-3 px-3 py-3 rounded-lg text-on-surface-variant opacity-40 cursor-not-allowed select-none"
                 title="Support portal coming soon">
                <span class="material-symbols-outlined" style="font-size: 20px;">help</span>
                <span class="font-label-md text-label-md">Support</span>
            </div>

            {{-- User profile card (Stitch: bg-white/5 rounded-xl, avatar initial, name, role) --}}
            <div class="flex items-center gap-3 px-3 py-4 mt-3 bg-white/5 rounded-xl">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center
                            text-on-secondary text-sm font-bold flex-shrink-0 border border-secondary-fixed/30">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="font-label-md text-label-md text-white truncate leading-snug">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-[10px] text-on-primary-container truncate uppercase tracking-wider mt-0.5">
                        {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                    </p>
                </div>
            </div>

            {{-- Sign out --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg
                               text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5
                               transition-colors text-left">
                    <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                    <span class="font-label-md text-label-md">Sign Out</span>
                </button>
            </form>

        </div>
        @endauth

    </aside>

    {{-- ── Main content column ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- ── Top header (Stitch: h-16 surface-container-lowest, GVOS brand + search | nav + actions) --}}
        <header class="sticky top-0 h-16 bg-surface-container-lowest border-b border-border-subtle shadow-sm
                       px-gutter flex items-center justify-between z-40"
                style="background-color:#ffffff">

            {{-- Left: GVOS bold brand text + search bar --}}
            <div class="flex items-center gap-8">
                <span class="font-headline-md text-headline-md font-black text-secondary leading-none">GVOS</span>
                <div class="relative hidden lg:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                          style="font-size: 18px;">search</span>
                    <input type="text"
                           class="bg-surface-container-low border-none rounded-full pl-10 pr-4 py-2 w-64
                                  font-body-sm text-body-sm focus:ring-2 focus:ring-secondary transition-all outline-none"
                           placeholder="Search workspace...">
                </div>
            </div>

            {{-- Right: nav links + icons + Clock In --}}
            @auth
            @php
                $headerActiveTimer = \App\Models\WorkspaceTimeLog::activeTimerFor(auth()->user());
                $headerTimerUrl = $headerActiveTimer?->workspace
                    ? route('workspace.time-logs.show', [$headerActiveTimer->workspace, $headerActiveTimer])
                    : (auth()->user()->hasRole('talent') ? route('talent.dashboard') : route('workspace.index'));
            @endphp
            <div class="flex items-center gap-6">

                {{-- Quick nav links (Stitch: Workspace active, Messages + Files as hints) --}}
                <nav class="hidden md:flex items-center gap-5">
                    <a href="{{ route('workspace.index') }}"
                       class="font-label-md text-label-md transition-all pb-0.5
                              {{ $workspaceActive
                                   ? 'text-secondary border-b-2 border-secondary font-bold'
                                   : 'text-outline hover:text-primary' }}">
                        Workspace
                    </a>
                    {{-- Messages and Files route to workspace list — universal context not available in shared layout --}}
                    <a href="{{ route('workspace.index') }}"
                       class="font-label-md text-label-md text-outline hover:text-primary transition-all"
                       title="Access messages from your workspace">
                        Messages
                    </a>
                    <a href="{{ route('workspace.index') }}"
                       class="font-label-md text-label-md text-outline hover:text-primary transition-all"
                       title="Access files from your workspace">
                        Files
                    </a>
                </nav>

                {{-- Notifications bell --}}
                <button type="button"
                        class="p-2 text-outline hover:bg-surface-container-low rounded-full transition-all"
                        title="Notifications">
                    <span class="material-symbols-outlined" style="font-size: 20px;">notifications</span>
                </button>

                {{-- Vertical divider --}}
                <div class="h-6 w-px bg-border-subtle"></div>

                {{-- Clock In button --}}
                {{-- Active timer entry point --}}
                <a href="{{ $headerTimerUrl }}"
                   class="bg-secondary text-on-secondary px-4 py-2 rounded-lg font-label-md text-label-md hover:brightness-110 transition-all inline-flex items-center gap-2"
                   title="{{ $headerActiveTimer ? 'View your active timer' : 'Go to your workspace to log time' }}">
                    @if ($headerActiveTimer)
                        <span class="material-symbols-outlined" style="font-size:15px;">timer</span>
                        Timer Running
                    @else
                        Clock In
                    @endif
                </a>

            </div>
            @endauth

        </header>

        {{-- ── Page content --}}
        <main class="flex-1 p-8" style="background-color:#F8FAFC">
            {{ $slot }}
        </main>

    </div>

</div>

</body>
</html>
