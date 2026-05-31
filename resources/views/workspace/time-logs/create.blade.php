<x-layouts.gvos :title="$workspace->name . ' — Log Time'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.time-logs.index', $workspace) }}" class="hover:text-secondary transition-colors">Time Logs</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Log Time</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">schedule</span>
            Log Work Time
        </h2>
        <a href="{{ route('workspace.time-logs.index', $workspace) }}"
           class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
            Back
        </a>
    </div>

    {{-- ── Validation errors ─────────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── Form ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
        <form method="POST" action="{{ route('workspace.time-logs.store', $workspace) }}" class="space-y-5">
            @csrf

            {{-- Date --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="log_date"
                       value="{{ old('log_date', now()->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       required>
            </div>

            {{-- Work summary --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Work Summary <span class="text-red-500">*</span></label>
                <input type="text" name="work_summary"
                       value="{{ old('work_summary') }}"
                       placeholder="Brief description of work completed"
                       maxlength="1000"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       required>
            </div>

            {{-- Task (optional) --}}
            @if ($tasks->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Related Task (optional)</label>
                    <select name="workspace_task_id"
                            class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                        <option value="">— No specific task —</option>
                        @foreach ($tasks as $task)
                            <option value="{{ $task->id }}" {{ old('workspace_task_id') == $task->id ? 'selected' : '' }}>
                                {{ $task->task_code }} — {{ $task->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Time range --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Start Time (optional)</label>
                    <input type="time" name="started_at"
                           value="{{ old('started_at') }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">End Time (optional)</label>
                    <input type="time" name="ended_at"
                           value="{{ old('ended_at') }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                </div>
            </div>

            {{-- Duration override --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Duration (minutes, optional override)</label>
                <input type="number" name="duration_minutes"
                       value="{{ old('duration_minutes') }}"
                       min="1" max="1440"
                       placeholder="e.g. 90"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                <p class="text-xs text-outline mt-1">Leave blank to auto-calculate from start/end time.</p>
            </div>

            {{-- Work details --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Work Details (optional)</label>
                <textarea name="work_details" rows="4"
                          maxlength="5000"
                          placeholder="Detailed notes about tasks completed, blockers, etc."
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('work_details') }}</textarea>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Save as</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="draft"
                               {{ old('status', 'draft') === 'draft' ? 'checked' : '' }}>
                        Draft
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="submitted"
                               {{ old('status') === 'submitted' ? 'checked' : '' }}>
                        Submit for review
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all"
                        style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Save Log
                </button>
                <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all"
                   style="border-color:#E2E8F0; color:#64748B;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</x-layouts.gvos>
