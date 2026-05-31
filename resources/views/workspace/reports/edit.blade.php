<x-layouts.gvos :title="$workspace->name . ' — Edit Report'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.reports.index', $workspace) }}" class="hover:text-secondary transition-colors">Weekly Reports</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}" class="hover:text-secondary transition-colors">{{ $report->weekLabel() }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Edit</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">edit</span>
            Edit Weekly Report
        </h2>
        <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
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
        <form method="POST" action="{{ route('workspace.reports.update', [$workspace, $report]) }}" class="space-y-5">
            @csrf @method('PUT')

            {{-- Week dates --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Week Start <span class="text-red-500">*</span></label>
                    <input type="date" name="week_start_date"
                           value="{{ old('week_start_date', $report->week_start_date->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Week End <span class="text-red-500">*</span></label>
                    <input type="date" name="week_end_date"
                           value="{{ old('week_end_date', $report->week_end_date->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                           required>
                </div>
            </div>

            {{-- Total minutes --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Total Minutes Logged</label>
                <input type="number" name="total_minutes"
                       value="{{ old('total_minutes', $report->total_minutes) }}"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
            </div>

            {{-- Summary --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Summary <span class="text-red-500">*</span></label>
                <textarea name="summary" rows="4" maxlength="5000" required
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('summary', $report->summary) }}</textarea>
            </div>

            {{-- Achievements --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Achievements (optional)</label>
                <textarea name="achievements" rows="3" maxlength="3000"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('achievements', $report->achievements) }}</textarea>
            </div>

            {{-- Blockers --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Blockers (optional)</label>
                <textarea name="blockers" rows="3" maxlength="3000"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('blockers', $report->blockers) }}</textarea>
            </div>

            {{-- Next steps --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Next Steps (optional)</label>
                <textarea name="next_steps" rows="3" maxlength="3000"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('next_steps', $report->next_steps) }}</textarea>
            </div>

            {{-- Client notes --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Client Notes (optional)</label>
                <textarea name="client_notes" rows="3" maxlength="3000"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('client_notes', $report->client_notes) }}</textarea>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Status</label>
                <div class="flex items-center gap-4 flex-wrap">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="draft"
                               {{ old('status', $report->status) === 'draft' ? 'checked' : '' }}>
                        Draft
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="submitted"
                               {{ old('status', $report->status) === 'submitted' ? 'checked' : '' }}>
                        Submit
                    </label>
                    @if (in_array($role, ['admin', 'workspace_admin', 'manager']))
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="status" value="approved"
                                   {{ old('status', $report->status) === 'approved' ? 'checked' : '' }}>
                            Approved
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="status" value="published"
                                   {{ old('status', $report->status) === 'published' ? 'checked' : '' }}>
                            Published
                        </label>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all"
                        style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Save Changes
                </button>
                <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all"
                   style="border-color:#E2E8F0; color:#64748B;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</x-layouts.gvos>
