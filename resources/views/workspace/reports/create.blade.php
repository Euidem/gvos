<x-layouts.gvos :title="$workspace->name . ' — New Weekly Report'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.reports.index', $workspace) }}" class="hover:text-secondary transition-colors">Weekly Reports</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>New Report</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">summarize</span>
            New Weekly Report
        </h2>
        <a href="{{ route('workspace.reports.index', $workspace) }}"
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

    {{-- ── Auto-computed hint --}}
    @if ($suggestedMinutes > 0)
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(0,88,190,0.05);border:1px solid rgba(0,88,190,0.15);color:#1D4ED8;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">info</span>
            {{ intdiv($suggestedMinutes, 60) }}h {{ $suggestedMinutes % 60 }}m of approved time logs found for the suggested week.
        </div>
    @endif

    {{-- ── Form ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
        <form method="POST" action="{{ route('workspace.reports.store', $workspace) }}" class="space-y-5">
            @csrf

            {{-- Week dates --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Week Start <span class="text-red-500">*</span></label>
                    <input type="date" name="week_start_date"
                           value="{{ old('week_start_date', $suggestedStart) }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Week End <span class="text-red-500">*</span></label>
                    <input type="date" name="week_end_date"
                           value="{{ old('week_end_date', $suggestedEnd) }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                           required>
                </div>
            </div>

            {{-- Total minutes --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Total Minutes Logged</label>
                <input type="number" name="total_minutes"
                       value="{{ old('total_minutes', $suggestedMinutes) }}"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                <p class="text-xs text-outline mt-1">Auto-filled from approved time logs for the suggested week.</p>
            </div>

            {{-- Summary --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Summary <span class="text-red-500">*</span></label>
                <textarea name="summary" rows="4" maxlength="5000" required
                          placeholder="Overall summary of work completed this week…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('summary') }}</textarea>
            </div>

            {{-- Achievements --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Achievements (optional)</label>
                <textarea name="achievements" rows="3" maxlength="3000"
                          placeholder="Key wins and milestones this week…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('achievements') }}</textarea>
            </div>

            {{-- Blockers --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Blockers (optional)</label>
                <textarea name="blockers" rows="3" maxlength="3000"
                          placeholder="Issues or blockers encountered…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('blockers') }}</textarea>
            </div>

            {{-- Next steps --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Next Steps (optional)</label>
                <textarea name="next_steps" rows="3" maxlength="3000"
                          placeholder="Plan for the upcoming week…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('next_steps') }}</textarea>
            </div>

            {{-- Client notes --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Client Notes (optional)</label>
                <textarea name="client_notes" rows="3" maxlength="3000"
                          placeholder="Notes specifically for the client…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('client_notes') }}</textarea>
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
                        Submit
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all"
                        style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Save Report
                </button>
                <a href="{{ route('workspace.reports.index', $workspace) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all"
                   style="border-color:#E2E8F0; color:#64748B;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</x-layouts.gvos>
