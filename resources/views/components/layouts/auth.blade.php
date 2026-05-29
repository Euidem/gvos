@props(['title' => 'Sign in', 'variant' => 'dark'])
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
                        "on-surface-variant": "#45464d",
                        "secondary-fixed":    "#d8e2ff",
                        "sidebar-bg":         "#0B0F19",
                        "surface-container-low":     "#f2f4f6",
                        "surface-container-lowest":  "#ffffff",
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
                        "on-primary-container": "#7c839b",
                        "secondary-container":  "#2170e4",
                        "border-subtle":     "#E2E8F0",
                        "outline":           "#76777d",
                        "secondary":         "#0058be",
                        "on-secondary":      "#ffffff",
                        "surface":           "#f7f9fb",
                        "background":        "#f7f9fb",
                        "primary-container": "#131b2e",
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
                        "gutter":      "24px",
                        "input-gap":   "16px",
                        "card-padding":"24px"
                    },
                    fontFamily: {
                        "headline-lg": ["Manrope", "sans-serif"],
                        "headline-md": ["Manrope", "sans-serif"],
                        "display-lg":  ["Manrope", "sans-serif"],
                        "label-md":    ["Inter", "sans-serif"],
                        "body-md":     ["Inter", "sans-serif"],
                        "body-sm":     ["Inter", "sans-serif"],
                        "mono-sm":     ["JetBrains Mono", "monospace"],
                        "sans":        ["Inter", "ui-sans-serif", "system-ui"]
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
        .bg-dot-pattern {
            background-color: #f7f9fb;
            background-image: radial-gradient(#e2e8f0 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
        .focused-input:focus-within {
            box-shadow: 0 0 0 2px rgba(0, 88, 190, 0.15);
        }
        /* ── GVOS Design Token CSS Fallback ────────────────────────────────────
           Ensures GVOS custom Tailwind tokens render even if the CDN JIT misses
           them. These rules are intentional and permanent. Do NOT remove.
           ──────────────────────────────────────────────────────────────────── */
        .bg-sidebar-bg{background-color:#0B0F19}
        .text-secondary-fixed{color:#d8e2ff}
        .text-on-primary-container{color:#7c839b}
        .bg-secondary-container{background-color:#2170e4}
        .bg-secondary{background-color:#0058be}
        .text-secondary{color:#0058be}
        .border-secondary{border-color:#0058be}
        .border-secondary-fixed{border-color:#d8e2ff}
        .text-on-secondary{color:#ffffff}
        .bg-surface,.bg-background{background-color:#f7f9fb}
        .bg-surface-container-low{background-color:#f2f4f6}
        .bg-surface-container-lowest{background-color:#ffffff}
        .bg-primary-container{background-color:#131b2e}
        .text-on-surface{color:#191c1e}
        .text-on-surface-variant{color:#45464d}
        .text-outline{color:#76777d}
        .border-border-subtle{border-color:#E2E8F0}
        .shadow-card{box-shadow:0px 4px 20px rgba(0,0,0,.04)}
        .bg-status-active{background-color:#10B981}.text-status-active{color:#10B981}
        .bg-status-completed{background-color:#059669}.text-status-completed{color:#059669}
        .bg-status-payment-due{background-color:#F59E0B}.text-status-payment-due{color:#F59E0B}
        .bg-status-blocked{background-color:#EF4444}.text-status-blocked{color:#EF4444}
        .bg-status-trial{background-color:#8B5CF6}.text-status-trial{color:#8B5CF6}
        .bg-secondary\/5{background-color:rgba(0,88,190,.05)}
        .bg-secondary\/10{background-color:rgba(0,88,190,.1)}
        .border-secondary\/20{border-color:rgba(0,88,190,.2)}
        .bg-status-active\/10{background-color:rgba(16,185,129,.1)}
        .border-status-active\/20{border-color:rgba(16,185,129,.2)}
        .bg-status-active\/20{background-color:rgba(16,185,129,.2)}
        .bg-status-blocked\/10{background-color:rgba(239,68,68,.1)}
        .border-status-blocked\/20{border-color:rgba(239,68,68,.2)}
        .text-secondary-fixed\/70{color:rgba(216,226,255,.7)}
        .focus\:ring-secondary\/20:focus{box-shadow:0 0 0 2px rgba(0,88,190,.2)}
        .focus\:border-secondary:focus{border-color:#0058be}
        .hover\:brightness-110:hover{filter:brightness(1.1)}
        .active\:scale-\[0\.98\]:active{transform:scale(.98)}
    </style>
</head>
<body class="h-full font-sans antialiased flex items-center justify-center min-h-screen px-4 {{ $variant === 'light' ? 'bg-dot-pattern' : 'bg-sidebar-bg' }}">
    <!-- GVOS UI Fidelity v2 active -->
    {{ $slot }}
</body>
</html>
