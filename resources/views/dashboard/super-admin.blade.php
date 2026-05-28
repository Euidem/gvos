<x-layouts.gvos title="Super Admin Dashboard">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">GVOS Ops Console</h2>
        <p class="text-sm text-slate-500 mt-1">Super Administrator — full system access</p>
    </div>

    {{-- Phase 0 placeholder stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach ([
            ['label' => 'Total Users',       'value' => '—', 'color' => 'indigo'],
            ['label' => 'Active Talent',      'value' => '—', 'color' => 'emerald'],
            ['label' => 'Active Clients',     'value' => '—', 'color' => 'sky'],
            ['label' => 'Open Tasks',         'value' => '—', 'color' => 'amber'],
        ] as $stat)
        <div class="bg-white rounded-xl border border-slate-200 px-6 py-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $stat['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Phase 0 notice --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-6 py-5 text-sm text-indigo-700">
        <strong class="font-semibold">Phase 0 — Foundation</strong>
        <p class="mt-1 text-indigo-600">
            GVOS is running successfully. Role-based routing is active. Module dashboards will be built in Phase 1.
        </p>
    </div>

</x-layouts.gvos>
