{{-- Legacy layout — redirects to the canonical component layout. --}}
{{-- All active views use <x-layouts.gvos>. This file is kept for safety. --}}
<x-layouts.gvos :title="$title ?? 'Dashboard'">
    {{ $slot ?? '' }}
</x-layouts.gvos>
