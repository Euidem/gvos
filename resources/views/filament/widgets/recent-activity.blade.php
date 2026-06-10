<x-filament-widgets::widget class="fi-wi-recent-activity">
    <x-filament::section>
        <x-slot name="heading">Recent Activity</x-slot>
        <x-slot name="description">Latest 10 audit events across the platform</x-slot>

        @php
            $events = $this->getRecentEvents();
        @endphp

        @if ($events->isEmpty())
            <p class="text-sm text-gray-500 py-2">No audit events recorded yet.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($events as $event)
                    <div class="flex items-start gap-3 py-2.5">
                        <div class="mt-0.5 flex-shrink-0">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100">
                                <x-filament::icon icon="heroicon-m-bolt" class="h-3.5 w-3.5 text-gray-500" />
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                <span class="text-sm font-medium text-gray-900 truncate">
                                    {{ str_replace(['.', '_'], ' ', $event['action']) }}
                                </span>
                                @if ($event['workspace'])
                                    <span class="text-xs text-gray-500">— {{ $event['workspace'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-400">{{ $event['actor'] }}</span>
                                <span class="text-gray-300">&middot;</span>
                                <span class="text-xs text-gray-400" title="{{ $event['created_at']->format('Y-m-d H:i:s') }}">
                                    {{ $event['created_at']->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <a href="{{ route('filament.admin.resources.audit-logs.index') }}" class="text-xs text-primary-600 hover:text-primary-800 font-medium">
                    View all audit logs &rarr;
                </a>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
