{{-- Legacy layout — redirects to the canonical component layout. --}}
{{-- All active views use <x-layouts.auth>. This file is kept for safety. --}}
<x-layouts.auth :title="$title ?? 'Sign in'">
    {{ $slot ?? '' }}
</x-layouts.auth>
