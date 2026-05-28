<x-layouts.gvos title="Manager Dashboard">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Manager Dashboard</h2>
        <p class="text-sm text-slate-500 mt-1">Line Manager — team oversight</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @foreach ([
            ['label' => 'My Talent',         'value' => '—'],
            ['label' => 'Active Tasks',      'value' => '—'],
            ['label' => 'Pending Reviews',   'value' => '—'],
        ] as $stat)
        <div class="bg-white rounded-xl border border-slate-200 px-6 py-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $stat['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-6 py-5 text-sm text-indigo-700">
        <strong class="font-semibold">Phase 0 — Foundation</strong>
        <p class="mt-1 text-indigo-600">
            Manager Console is active. Team management, task oversight, and reporting features are planned for Phase 1.
        </p>
    </div>

</x-layouts.gvos>
