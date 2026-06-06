<?php

namespace App\Notifications;

use App\Models\WorkspaceWeeklyReport;

class WeeklyReportPublishedNotification extends GvosNotification
{
    public function __construct(WorkspaceWeeklyReport $report)
    {
        parent::__construct('weekly_report_published', [
            'title' => 'Weekly report published',
            'message' => 'A weekly workspace report has been published for your review.',
            'action_url' => route('workspace.reports.show', [$report->workspace_id, $report->id]),
            'workspace_id' => $report->workspace_id,
            'related_type' => WorkspaceWeeklyReport::class,
            'related_id' => $report->id,
            'level' => 'success',
        ]);
    }
}
