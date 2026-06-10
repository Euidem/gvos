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

    @if ($report->wasGenerated())
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(124,58,237,0.05);border:1px solid rgba(124,58,237,0.2);color:#6D28D9;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">auto_awesome</span>
            This report was auto-generated from workspace data on {{ $report->generated_at?->format('d M Y \a\t H:i') }}.
            Review all sections before publishing — especially the Client Notes.
        </div>
    @endif

    <form method="POST" action="{{ route('workspace.reports.update', [$workspace, $report]) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ── Week dates + hours ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
            <h3 class="text-xs font-bold text-outline uppercase tracking-wide mb-4">Report Period</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Total Minutes Logged</label>
                    <input type="number" name="total_minutes"
                           value="{{ old('total_minutes', $report->total_minutes) }}"
                           min="0"
                           class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    <p class="text-xs text-outline mt-1">Used for internal hours display.</p>
                </div>
            </div>
        </div>

        {{-- ── CLIENT-VISIBLE SECTION ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-status-active" style="font-size:18px;">visibility</span>
                <h3 class="text-xs font-bold uppercase tracking-wide" style="color:#059669;">Client-Visible Sections</h3>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                      style="background:rgba(5,150,105,0.08);color:#059669;">Shown to client after publish</span>
            </div>

            {{-- Summary --}}
            <div class="mb-4">
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Summary <span class="text-red-500">*</span>
                    <span class="text-outline font-normal ml-1">— visible to client</span>
                </label>
                <textarea name="summary" rows="5" maxlength="5000" required
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('summary', $report->summary) }}</textarea>
            </div>

            {{-- Achievements --}}
            <div class="mb-4">
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Achievements
                    <span class="text-outline font-normal ml-1">— visible to client (optional)</span>
                </label>
                <textarea name="achievements" rows="4" maxlength="3000"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('achievements', $report->achievements) }}</textarea>
            </div>

            {{-- Client notes --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Client Notes
                    <span class="text-outline font-normal ml-1">— shown to client only (optional)</span>
                </label>
                <textarea name="client_notes" rows="3" maxlength="3000"
                          placeholder="A message specifically for your client, e.g. action items or context…"
                          class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y">{{ old('client_notes', $report->client_notes) }}</textarea>
            </div>
        </div>

        {{-- ── INTERNAL-ONLY SECTION ───────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-[#FEF3C7] shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined" style="font-size:18px; color:#D97706;">lock</span>
                <h3 class="text-xs font-bold uppercase tracking-wide" style="color:#D97706;">Internal Sections</h3>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                      style="background:rgba(217,119,6,0.08);color:#D97706;">Manager &amp; Admin only — never shown to client</span>
            </div>

            {{-- Blockers --}}
            <div class="mb-4">
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Blockers
                    <span class="font-normal ml-1" style="color:#D97706;">— internal only</span>
                </label>
                <textarea name="blockers" rows="3" maxlength="3000"
                          placeholder="Issues or blockers that should be resolved…"
                          class="w-full px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 resize-y"
                          style="border:1px solid #FDE68A; focus:ring-color:#D97706;">{{ old('blockers', $report->blockers) }}</textarea>
            </div>

            {{-- Next steps --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Next Steps
                    <span class="font-normal ml-1" style="color:#D97706;">— internal only</span>
                </label>
                <textarea name="next_steps" rows="3" maxlength="3000"
                          placeholder="Planned tasks and actions for the coming week…"
                          class="w-full px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 resize-y"
                          style="border:1px solid #FDE68A;">{{ old('next_steps', $report->next_steps) }}</textarea>
            </div>
        </div>

        {{-- ── Status + Actions ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-on-surface mb-2">Save as</label>
                <div class="flex items-center gap-4 flex-wrap">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="draft"
                               {{ old('status', $report->status) === 'draft' ? 'checked' : '' }}>
                        Draft
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="status" value="submitted"
                               {{ old('status', $report->status) === 'submitted' ? 'checked' : '' }}>
                        Submit for Review
                    </label>
                    @if (in_array($role, ['admin', 'workspace_admin', 'manager']))
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="status" value="approved"
                                   {{ old('status', $report->status) === 'approved' ? 'checked' : '' }}>
                            Approved
                        </label>
                    @endif
                </div>
                <p class="text-xs text-outline mt-2">
                    To publish to the client, save as Approved and then use the "Publish to Client" button on the report page.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-[#F1F5F9]">
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
        </div>

    </form>

</x-layouts.gvos>
