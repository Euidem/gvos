<?php

namespace App\Notifications;

use App\Models\WorkspaceWeeklyReport;

/**
 * Phase 17: Notifies workspace managers/admins when a report draft is auto-generated.
 * Never sent to clients.
 */
class WeeklyReportGeneratedNotification extends GvosNotification
{
    public function __construct(WorkspaceWeeklyReport $report)
    {
        parent::__construct('weekly_report_generated', [
            'title'        => 'Weekly report draft generated',
            'message'      => 'A draft weekly report has been auto-generated for ' . ($report->workspace?->name ?? 'your workspace') . '. Review and publish when ready.',
            'action_url'   => route('workspace.reports.edit', [$report->workspace_id, $report->id]),
            'workspace_id' => $report->workspace_id,
            'related_type' => WorkspaceWeeklyReport::class,
            'related_id'   => $report->id,
            'level'        => 'info',
        ]);
    }
}
