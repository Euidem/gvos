@props(['title' => 'GVOS'])
<!DOCTYPE html>
<html lang="en">
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
                        "sidebar-bg":                "#0B0F19",
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
    </style>
</head>
<body class="bg-sidebar-bg font-sans antialiased min-h-screen">
    <div class="py-10 px-4">
        {{ $slot }}
    </div>
</body>
</html>
