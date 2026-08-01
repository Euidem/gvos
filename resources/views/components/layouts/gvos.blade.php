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
                        "card-padding":      "20px",
                        "section-gap":       "32px"
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
                        "headline-lg": ["26px", {"lineHeight": "32px", "fontWeight": "700", "letterSpacing": "-0.015em"}],
                        "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "700", "letterSpacing": "-0.01em"}],
                        "body-lg":     ["17px", {"lineHeight": "26px", "fontWeight": "400"}],
                        "body-md":     ["15px", {"lineHeight": "23px", "fontWeight": "400"}],
                        "body-sm":     ["13.5px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-md":    ["12.5px", {"lineHeight": "16px", "fontWeight": "600", "letterSpacing": "0.01em"}],
                        "mono-sm":     ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "display-lg":  ["40px", {"lineHeight": "48px", "fontWeight": "800", "letterSpacing": "-0.02em"}]
                    },
                    boxShadow: {
                        "card": "0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)",
                        "subtle": "0 1px 2px rgba(16,24,40,0.04)"
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
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(16,24,40,0.08);
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
        .shadow-card{box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.06)}
        .shadow-subtle{box-shadow:0 1px 2px rgba(16,24,40,.04)}
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

        /* ── Phase 28 portal shell ─────────────────────────────────────────── */
        .gvos-nav-item{display:flex;align-items:center;gap:12px;padding:9px 12px;border-radius:8px;
            color:#9aa2b4;transition:background-color .15s ease,color .15s ease;position:relative}
        .gvos-nav-item:hover{color:#d8e2ff;background-color:rgba(255,255,255,.06)}
        .gvos-nav-item.is-active{color:#ffffff;background-color:rgba(255,255,255,.10);font-weight:600}
        .gvos-nav-item.is-active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;
            width:3px;border-radius:0 3px 3px 0;background:#adc6ff}
        .gvos-group-label{font-size:11px;font-weight:600;letter-spacing:.04em;color:#5f6879;
            padding:0 12px;margin:18px 0 6px}
        /* Workspace tabs */
        .gvos-tabs{display:flex;gap:2px;overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch}
        .gvos-tabs::-webkit-scrollbar{display:none}
        .gvos-tab{display:inline-flex;align-items:center;gap:7px;padding:10px 12px;white-space:nowrap;
            font-size:13.5px;font-weight:500;color:#5b6472;border-bottom:2px solid transparent;transition:color .15s ease,border-color .15s ease}
        .gvos-tab:hover{color:#0058be}
        .gvos-tab.is-active{color:#0058be;border-bottom-color:#0058be;font-weight:600}
        /* Mobile sidebar */
        @media(max-width:1023px){
            #gvos-sidebar{position:fixed;top:0;left:0;height:100dvh;overflow-y:auto;transform:translateX(-100%);
                transition:transform .26s cubic-bezier(.4,0,.2,1);z-index:60}
            #gvos-sidebar.gvos-sidebar-open{transform:translateX(0)}
            #gvos-sidebar-backdrop{position:fixed;inset:0;background:rgba(11,15,25,.5);z-index:55;opacity:0;
                pointer-events:none;transition:opacity .26s ease}
            #gvos-sidebar-backdrop.gvos-backdrop-visible{opacity:1;pointer-events:auto}
        }
        @media(min-width:1024px){#gvos-menu-btn{display:none}}
        /* Tap targets on touch devices */
        @media(max-width:767px){.gvos-nav-item{padding:12px}.gvos-tab{padding:12px 14px}}
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

@php
    use App\Support\Portal\PortalNav;

    $__user = auth()->user();

    // Workspace context — set when the current route is workspace-scoped.
    $__ws     = request()->route('workspace');
    $__ws     = $__ws instanceof \App\Models\Workspace ? $__ws : null;
    $__wsRole = ($__ws && $__user) ? $__ws->resolveUserWorkspaceRole($__user) : null;
    $__wsTabs = ($__ws && $__wsRole && $__wsRole !== 'none') ? PortalNav::workspaceTabs($__ws, $__wsRole) : [];

    $__groups = $__user ? PortalNav::sidebar($__user) : [];

    // Running timer chip — only queried for roles that can actually log time.
    $__timer = null;
    if ($__user && $__user->hasAnyRole(['talent', 'line_manager', 'super_admin', 'operations_admin'])) {
        $__timer = \App\Models\WorkspaceTimeLog::activeTimerFor($__user);
    }

    $__unread = $__user ? $__user->unreadNotifications()->count() : 0;
@endphp

<div class="min-h-screen flex">

    {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
    {{-- Visual Repair v3: inline style is the structural fallback for #0B0F19 --}}
    <aside id="gvos-sidebar" class="w-[260px] text-white flex flex-col flex-shrink-0 min-h-screen py-5 px-3"
           style="background-color:#0B0F19">

        {{-- Brand --}}
        <a href="{{ $__user ? PortalNav::homeUrl($__user) : url('/') }}"
           class="mb-2 px-2 flex items-center gap-3 group">
            <div class="w-9 h-9 bg-secondary-container rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-on-secondary"
                      style="font-variation-settings: 'FILL' 1; font-size: 19px;">hub</span>
            </div>
            <div class="min-w-0">
                <p class="font-headline-md text-[17px] font-bold text-white leading-none">GVOS</p>
                @auth
                    <p class="text-[11px] text-on-primary-container mt-1 truncate">{{ PortalNav::roleLabel($__user) }}</p>
                @endauth
            </div>
        </a>

        @auth
        {{-- Navigation groups --}}
        <nav class="flex-1 overflow-y-auto mt-4 -mx-1 px-1">
            @foreach ($__groups as $__group)
                @if ($__group['label'])
                    <p class="gvos-group-label">{{ $__group['label'] }}</p>
                @endif
                <div class="space-y-0.5">
                    @foreach ($__group['items'] as $__item)
                        <a href="{{ $__item['href'] }}"
                           class="gvos-nav-item {{ $__item['active'] ? 'is-active' : '' }}"
                           @if ($__item['active']) aria-current="page" @endif>
                            <span class="material-symbols-outlined flex-shrink-0" style="font-size:19px;">{{ $__item['icon'] }}</span>
                            <span class="text-[13.5px] leading-none">{{ $__item['label'] }}</span>
                            @if ($__item['label'] === 'Notifications' && $__unread > 0)
                                <span class="ml-auto flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold leading-none text-white"
                                      style="background-color:#EF4444;">{{ $__unread > 9 ? '9+' : $__unread }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        {{-- Account footer --}}
        <div class="mt-4 pt-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center
                            text-on-secondary text-[12px] font-bold flex-shrink-0">
                    {{ strtoupper(substr($__user->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-white truncate leading-tight">{{ $__user->name }}</p>
                    <p class="text-[11px] text-on-primary-container truncate">{{ $__user->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="gvos-nav-item w-full text-left">
                    <span class="material-symbols-outlined flex-shrink-0" style="font-size:19px;">logout</span>
                    <span class="text-[13.5px] leading-none">Sign out</span>
                </button>
            </form>
        </div>
        @endauth

    </aside>

    {{-- ── Mobile sidebar backdrop ──────────────────────────────────────────── --}}
    <div id="gvos-sidebar-backdrop" onclick="gvosCloseSidebar()"></div>

    {{-- ── Main content column ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- ── Top bar: context on the left, status on the right ───────────── --}}
        <header class="sticky top-0 h-14 bg-surface-container-lowest border-b border-border-subtle
                       px-4 sm:px-6 flex items-center justify-between gap-3 z-40"
                style="background-color:#ffffff">

            <div class="flex items-center gap-2 min-w-0">
                <button id="gvos-menu-btn" type="button" onclick="gvosToggleSidebar()"
                        class="p-2 -ml-2 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors flex-shrink-0"
                        aria-label="Open navigation menu">
                    <span class="material-symbols-outlined" style="font-size:22px;">menu</span>
                </button>
                {{-- Current location. Workspace context takes priority over page title. --}}
                <div class="min-w-0">
                    @if ($__ws)
                        <p class="text-[11px] text-outline leading-none mb-0.5">Workspace</p>
                        <p class="text-[14px] font-semibold text-on-surface truncate leading-tight">{{ $__ws->name }}</p>
                    @else
                        <p class="text-[14px] font-semibold text-on-surface truncate leading-tight">{{ $title }}</p>
                    @endif
                </div>
            </div>

            @auth
            <div class="flex items-center gap-2 flex-shrink-0">

                {{-- Running timer chip — shown only when a timer is genuinely running --}}
                @if ($__timer && $__timer->workspace)
                    <a href="{{ route('workspace.time-logs.show', [$__timer->workspace, $__timer]) }}"
                       class="hidden sm:inline-flex items-center gap-2 pl-2.5 pr-3 py-1.5 rounded-full text-[12.5px] font-semibold transition-all hover:brightness-105"
                       style="background:rgba(16,185,129,0.10);color:#047857;border:1px solid rgba(16,185,129,0.25);"
                       title="You have a work session running">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#10B981;"></span>
                        <span class="js-running-timer font-mono-sm" data-started-at="{{ $__timer->started_at?->toIso8601String() }}">
                            {{ $__timer->durationForHumans() }}
                        </span>
                    </a>
                @endif

                {{-- Notifications --}}
                <a href="{{ route('notifications.index') }}"
                   class="relative p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors"
                   aria-label="Notifications{{ $__unread > 0 ? ' (' . $__unread . ' unread)' : '' }}">
                    <span class="material-symbols-outlined" style="font-size:21px;">notifications</span>
                    @if ($__unread > 0)
                        <span class="absolute right-1 top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full px-1 text-[9px] font-bold leading-none text-white"
                              style="background-color:#EF4444;">{{ $__unread > 9 ? '9+' : $__unread }}</span>
                    @endif
                </a>
            </div>
            @endauth

        </header>

        {{-- ── Workspace sub-navigation (only inside a workspace) ──────────── --}}
        @if ($__wsTabs)
            <div class="bg-surface-container-lowest border-b border-border-subtle px-4 sm:px-6 sticky top-14 z-30"
                 style="background-color:#ffffff">
                <div class="max-w-[1280px] mx-auto">
                    <nav class="gvos-tabs" aria-label="Workspace sections">
                        @foreach ($__wsTabs as $__tab)
                            <a href="{{ $__tab['href'] }}"
                               class="gvos-tab {{ $__tab['active'] ? 'is-active' : '' }}"
                               @if ($__tab['active']) aria-current="page" @endif>
                                <span class="material-symbols-outlined" style="font-size:17px;">{{ $__tab['icon'] }}</span>
                                {{ $__tab['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        @endif

        {{-- ── Page content --}}
        <main class="flex-1 px-4 py-6 sm:px-6 sm:py-7 lg:px-8" style="background-color:#f7f9fb">
            <div class="max-w-[1280px] mx-auto w-full">
                {{-- Global flash stack (status / warning). success+error stay page-local. --}}
                <x-portal.flash />
                {{ $slot }}
            </div>
        </main>

    </div>

</div>

<script>
    function gvosToggleSidebar(){
        document.getElementById('gvos-sidebar').classList.toggle('gvos-sidebar-open');
        document.getElementById('gvos-sidebar-backdrop').classList.toggle('gvos-backdrop-visible');
    }
    function gvosCloseSidebar(){
        document.getElementById('gvos-sidebar').classList.remove('gvos-sidebar-open');
        document.getElementById('gvos-sidebar-backdrop').classList.remove('gvos-backdrop-visible');
    }
    document.addEventListener('DOMContentLoaded',function(){
        document.querySelectorAll('#gvos-sidebar a').forEach(function(a){
            a.addEventListener('click',function(){if(window.innerWidth<1024)gvosCloseSidebar();});
        });
        document.addEventListener('keydown',function(e){if(e.key==='Escape')gvosCloseSidebar();});

        // Live tick for any running-timer element (shell chip + page widgets).
        function fmt(s){
            var t=Math.max(0,Math.floor((Date.now()-new Date(s).getTime())/1000));
            return String(Math.floor(t/3600)).padStart(2,'0')+':'+
                   String(Math.floor((t%3600)/60)).padStart(2,'0')+':'+
                   String(t%60).padStart(2,'0');
        }
        document.querySelectorAll('.js-running-timer[data-started-at]').forEach(function(el){
            var tick=function(){el.textContent=fmt(el.dataset.startedAt);};
            tick(); setInterval(tick,1000);
        });

        // Keep the active workspace tab in view on small screens.
        var tabs=document.querySelector('.gvos-tabs .is-active');
        if(tabs&&tabs.scrollIntoView){tabs.scrollIntoView({inline:'center',block:'nearest'});}
    });
</script>
</body>
</html>
