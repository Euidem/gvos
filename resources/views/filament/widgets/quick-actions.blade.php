<x-filament-widgets::widget class="fi-wi-quick-actions">
    <x-filament::section>
        <x-slot name="heading">Quick Actions</x-slot>
        <x-slot name="description">Common admin tasks</x-slot>

        @php
            $actions = $this->getActions();
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
            @foreach ($actions as $action)
                @php
                    $btnColors = match($action['color']) {
                        'primary' => 'bg-primary-50 hover:bg-primary-100 text-primary-700 border-primary-200',
                        'warning' => 'bg-warning-50 hover:bg-warning-100 text-warning-700 border-warning-200',
                        'success' => 'bg-success-50 hover:bg-success-100 text-success-700 border-success-200',
                        'danger'  => 'bg-danger-50 hover:bg-danger-100 text-danger-700 border-danger-200',
                        'info'    => 'bg-info-50 hover:bg-info-100 text-info-700 border-info-200',
                        default   => 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200',
                    };
                @endphp
                <a href="{{ $action['url'] }}"
                   class="flex flex-col items-center justify-center gap-2 rounded-lg border px-3 py-3 text-center transition-colors {{ $btnColors }}">
                    <x-filament::icon :icon="$action['icon']" class="h-5 w-5 flex-shrink-0" />
                    <span class="text-xs font-medium leading-tight">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
