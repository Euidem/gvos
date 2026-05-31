<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceWeeklyReport;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class WorkspaceWeeklyReportController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve workspace role and abort 403 if the user has no access.
     */
    private function requireAccess(Request $request, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($request->user());

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * List weekly reports for this workspace.
     *
     * - Admin / workspace_admin / manager: see all statuses.
     * - Talent: see submitted, approved, published (not draft).
     * - Client roles: see published only.
     * - Observer: no access.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if ($role === 'observer') {
            abort(403, 'Observers cannot view weekly reports.');
        }

        $visibleStatuses = WorkspaceWeeklyReport::visibleStatusesFor($role);

        if (empty($visibleStatuses)) {
            abort(403, 'You do not have access to weekly reports.');
        }

        $reports = $workspace->weeklyReports()
            ->with(['preparedBy', 'reviewedBy'])
            ->whereIn('status', $visibleStatuses)
            ->orderByDesc('week_start_date')
            ->paginate(20);

        $canCreate = WorkspaceWeeklyReport::canCreate($role);
        $isClient  = in_array($role, ['client_admin', 'client_staff', 'client'], true);

        return view('workspace.reports.index', compact(
            'workspace', 'reports', 'role', 'canCreate', 'isClient'
        ));
    }

    /**
     * Show the create weekly report form.
     */
    public function create(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceWeeklyReport::canCreate($role)) {
            abort(403, 'You cannot create weekly reports in this workspace.');
        }

        // Suggest week boundaries (last Monday–Sunday)
        $suggestedStart = now()->startOfWeek()->subWeek()->format('Y-m-d');
        $suggestedEnd   = now()->startOfWeek()->subDay()->format('Y-m-d');

        // Compute total approved minutes for suggested week
        $suggestedMinutes = $workspace->timeLogs()
            ->where('status', 'approved')
            ->whereBetween('log_date', [$suggestedStart, $suggestedEnd])
            ->sum('duration_minutes');

        return view('workspace.reports.create', compact(
            'workspace', 'role', 'suggestedStart', 'suggestedEnd', 'suggestedMinutes'
        ));
    }

    /**
     * Store a new weekly report.
     */
    public function store(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceWeeklyReport::canCreate($role)) {
            abort(403, 'You cannot create weekly reports in this workspace.');
        }

        $validated = $request->validate([
            'week_start_date' => 'required|date',
            'week_end_date'   => 'required|date|after_or_equal:week_start_date',
            'summary'         => 'required|string|max:5000',
            'achievements'    => 'nullable|string|max:3000',
            'blockers'        => 'nullable|string|max:3000',
            'next_steps'      => 'nullable|string|max:3000',
            'client_notes'    => 'nullable|string|max:3000',
            'total_minutes'   => 'nullable|integer|min:0',
            'status'          => 'nullable|in:draft,submitted',
        ]);

        $report = WorkspaceWeeklyReport::create([
            'workspace_id'        => $workspace->id,
            'prepared_by_user_id' => $request->user()->id,
            'week_start_date'     => $validated['week_start_date'],
            'week_end_date'       => $validated['week_end_date'],
            'summary'             => $validated['summary'],
            'achievements'        => $validated['achievements'] ?? null,
            'blockers'            => $validated['blockers'] ?? null,
            'next_steps'          => $validated['next_steps'] ?? null,
            'client_notes'        => $validated['client_notes'] ?? null,
            'total_minutes'       => $validated['total_minutes'] ?? 0,
            'status'              => $validated['status'] ?? 'draft',
        ]);

        AuditLogger::weeklyReportCreated($report, [
            'workspace_code' => $workspace->workspace_code,
        ]);

        return redirect()
            ->route('workspace.reports.show', [$workspace, $report])
            ->with('success', 'Weekly report saved.');
    }

    /**
     * Show a single weekly report.
     */
    public function show(Request $request, Workspace $workspace, WorkspaceWeeklyReport $report)
    {
        $role = $this->requireAccess($request, $workspace);

        if ((int) $report->workspace_id !== (int) $workspace->id) {
            abort(404, 'Report not found in this workspace.');
        }

        if ($role === 'observer') {
            abort(403, 'Observers cannot view reports.');
        }

        $visibleStatuses = WorkspaceWeeklyReport::visibleStatusesFor($role);

        if (! in_array($report->status, $visibleStatuses, true)) {
            abort(403, 'This report is not available.');
        }

        $report->load(['preparedBy', 'reviewedBy']);
        $canEdit    = WorkspaceWeeklyReport::canCreate($role)
                      && in_array($report->status, ['draft', 'submitted'], true);
        $canApprove = WorkspaceWeeklyReport::canApprove($role)
                      && $report->status === 'submitted';
        $canPublish = WorkspaceWeeklyReport::canApprove($role)
                      && $report->status === 'approved';
        $isClient   = in_array($role, ['client_admin', 'client_staff', 'client'], true);

        return view('workspace.reports.show', compact(
            'workspace', 'report', 'role', 'canEdit', 'canApprove', 'canPublish', 'isClient'
        ));
    }

    /**
     * Show the edit form for a weekly report.
     */
    public function edit(Request $request, Workspace $workspace, WorkspaceWeeklyReport $report)
    {
        $role = $this->requireAccess($request, $workspace);

        if ((int) $report->workspace_id !== (int) $workspace->id) {
            abort(404, 'Report not found in this workspace.');
        }

        if (! WorkspaceWeeklyReport::canCreate($role)) {
            abort(403, 'You cannot edit weekly reports.');
        }

        if (! in_array($report->status, ['draft', 'submitted'], true)) {
            abort(403, 'This report cannot be edited once approved or published.');
        }

        return view('workspace.reports.edit', compact('workspace', 'report', 'role'));
    }

    /**
     * Update a weekly report.
     */
    public function update(Request $request, Workspace $workspace, WorkspaceWeeklyReport $report)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $report->workspace_id !== (int) $workspace->id) {
            abort(404, 'Report not found in this workspace.');
        }

        if (! WorkspaceWeeklyReport::canCreate($role)) {
            abort(403, 'You cannot edit weekly reports.');
        }

        $validated = $request->validate([
            'week_start_date' => 'required|date',
            'week_end_date'   => 'required|date|after_or_equal:week_start_date',
            'summary'         => 'required|string|max:5000',
            'achievements'    => 'nullable|string|max:3000',
            'blockers'        => 'nullable|string|max:3000',
            'next_steps'      => 'nullable|string|max:3000',
            'client_notes'    => 'nullable|string|max:3000',
            'total_minutes'   => 'nullable|integer|min:0',
            'status'          => 'nullable|in:draft,submitted,approved,published',
        ]);

        // When publishing, set published_at
        $publishedAt = $report->published_at;
        if (($validated['status'] ?? '') === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        $report->update([
            'week_start_date'     => $validated['week_start_date'],
            'week_end_date'       => $validated['week_end_date'],
            'summary'             => $validated['summary'],
            'achievements'        => $validated['achievements'] ?? null,
            'blockers'            => $validated['blockers'] ?? null,
            'next_steps'          => $validated['next_steps'] ?? null,
            'client_notes'        => $validated['client_notes'] ?? null,
            'total_minutes'       => $validated['total_minutes'] ?? $report->total_minutes,
            'status'              => $validated['status'] ?? $report->status,
            'reviewed_by_user_id' => WorkspaceWeeklyReport::canApprove($role)
                                        ? $user->id
                                        : $report->reviewed_by_user_id,
            'published_at'        => $publishedAt,
        ]);

        AuditLogger::weeklyReportUpdated($report, [
            'workspace_code' => $workspace->workspace_code,
            'updated_by'     => $user->id,
        ]);

        return redirect()
            ->route('workspace.reports.show', [$workspace, $report])
            ->with('success', 'Weekly report updated.');
    }

    /**
     * Soft-delete a weekly report (admin/manager only; only draft or submitted).
     */
    public function destroy(Request $request, Workspace $workspace, WorkspaceWeeklyReport $report)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $report->workspace_id !== (int) $workspace->id) {
            abort(404, 'Report not found in this workspace.');
        }

        if (! WorkspaceWeeklyReport::canCreate($role)) {
            abort(403, 'You cannot delete weekly reports.');
        }

        if (! in_array($report->status, ['draft', 'submitted'], true)) {
            abort(403, 'Approved or published reports cannot be deleted.');
        }

        $report->delete();

        AuditLogger::weeklyReportDeleted($report, [
            'workspace_code' => $workspace->workspace_code,
            'deleted_by'     => $user->id,
        ]);

        return redirect()
            ->route('workspace.reports.index', $workspace)
            ->with('success', 'Weekly report deleted.');
    }
}
