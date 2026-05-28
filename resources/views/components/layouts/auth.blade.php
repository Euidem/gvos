@props(['title' => 'Sign in'])
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
            theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] } } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-900 font-sans antialiased flex items-center justify-center min-h-screen px-4">
    {{ $slot }}
</body>
</html>
