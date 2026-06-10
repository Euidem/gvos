<x-filament-widgets::widget class="fi-wi-operational-alerts">
    <x-filament::section>
        <x-slot name="heading">Operational Alerts</x-slot>
        <x-slot name="description">Issues requiring admin attention</x-slot>

        @php
            $alerts = $this->getAlerts();
        @endphp

        @if (empty($alerts))
            <div class="flex items-center gap-3 rounded-lg bg-success-50 border border-success-200 px-4 py-3">
                <x-filament::icon
                    icon="heroicon-o-check-circle"
                    class="h-5 w-5 text-success-600 flex-shrink-0"
                />
                <p class="text-sm font-medium text-success-700">No active alerts — all systems look healthy.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($alerts as $alert)
                    @php
                        $colors = match($alert['severity']) {
                            'danger'  => ['bg' => 'bg-danger-50',   'border' => 'border-danger-200',   'text' => 'text-danger-700',   'badge' => 'bg-danger-100 text-danger-800',   'icon' => 'text-danger-600'],
                            'warning' => ['bg' => 'bg-warning-50',  'border' => 'border-warning-200',  'text' => 'text-warning-700',  'badge' => 'bg-warning-100 text-warning-800',  'icon' => 'text-warning-600'],
                            'info'    => ['bg' => 'bg-info-50',     'border' => 'border-info-200',     'text' => 'text-info-700',     'badge' => 'bg-info-100 text-info-800',         'icon' => 'text-info-600'],
                            default   => ['bg' => 'bg-gray-50',     'border' => 'border-gray-200',     'text' => 'text-gray-700',     'badge' => 'bg-gray-100 text-gray-800',         'icon' => 'text-gray-500'],
                        };
                    @endphp
                    <div class="flex items-start gap-3 rounded-lg {{ $colors['bg'] }} border {{ $colors['border'] }} px-4 py-3">
                        <x-filament::icon
                            :icon="$alert['icon']"
                            class="h-5 w-5 {{ $colors['icon'] }} flex-shrink-0 mt-0.5"
                        />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold {{ $colors['text'] }}">{{ $alert['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $colors['badge'] }}">
                                    {{ $alert['count'] }}
                                </span>
                            </div>
                            @if (isset($alert['link']))
                                <a href="{{ $alert['link'] }}" class="mt-1 text-xs {{ $colors['text'] }} underline hover:no-underline">
                                    {{ $alert['link_label'] ?? 'View' }} &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
