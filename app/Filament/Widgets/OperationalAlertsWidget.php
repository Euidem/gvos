<?php

namespace App\Filament\Widgets;

use App\Models\EmailDeliveryLog;
use App\Models\Invoice;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceSubscription;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceWeeklyReport;
use Filament\Widgets\Widget;

class OperationalAlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.operational-alerts';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = null;

    public function getAlerts(): array
    {
        $alerts = [];

        $overdueInv = Invoice::where('status', 'overdue')->count();
        if ($overdueInv > 0) {
            $alerts[] = [
                'label'     => 'Overdue Invoices',
                'count'     => $overdueInv,
                'severity'  => 'danger',
                'icon'      => 'heroicon-o-document-minus',
                'link'      => route('filament.admin.resources.invoices.index'),
                'link_label' => 'View Invoices',
            ];
        }

        $restricted = WorkspaceSubscription::whereNotNull('restricted_at')->whereNull('suspended_at')->count();
        if ($restricted > 0) {
            $alerts[] = [
                'label'     => 'Restricted Workspaces',
                'count'     => $restricted,
                'severity'  => 'danger',
                'icon'      => 'heroicon-o-lock-closed',
                'link'      => route('filament.admin.resources.workspace-subscriptions.index'),
                'link_label' => 'View Subscriptions',
            ];
        }

        $suspended = WorkspaceSubscription::where('status', 'suspended')->count();
        if ($suspended > 0) {
            $alerts[] = [
                'label'     => 'Suspended Workspaces',
                'count'     => $suspended,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-no-symbol',
                'link'      => route('filament.admin.resources.workspace-subscriptions.index'),
                'link_label' => 'View Subscriptions',
            ];
        }

        $longRunning = WorkspaceTimeLog::where('status', 'running')
            ->where('started_at', '<', now()->subHours(10))
            ->count();
        if ($longRunning > 0) {
            $alerts[] = [
                'label'     => 'Long-Running Timers (>10h)',
                'count'     => $longRunning,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-clock',
                'link'      => route('filament.admin.resources.workspace-time-logs.index'),
                'link_label' => 'View Time Logs',
            ];
        }

        $submittedLogs = WorkspaceTimeLog::where('status', 'submitted')->count();
        if ($submittedLogs > 0) {
            $alerts[] = [
                'label'     => 'Time Logs Awaiting Review',
                'count'     => $submittedLogs,
                'severity'  => 'info',
                'icon'      => 'heroicon-o-document-check',
                'link'      => route('filament.admin.resources.workspace-time-logs.index'),
                'link_label' => 'View Logs',
            ];
        }

        $draftReports = WorkspaceWeeklyReport::where('status', 'draft')->count();
        if ($draftReports > 0) {
            $alerts[] = [
                'label'     => 'Draft Reports Awaiting Publish',
                'count'     => $draftReports,
                'severity'  => 'info',
                'icon'      => 'heroicon-o-document-text',
                'link'      => route('filament.admin.resources.workspace-weekly-reports.index'),
                'link_label' => 'View Reports',
            ];
        }

        $staleInvitations = WorkspaceInvitation::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        if ($staleInvitations > 0) {
            $alerts[] = [
                'label'     => 'Stale Invitations (>7 days)',
                'count'     => $staleInvitations,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-envelope',
                'link'      => route('filament.admin.resources.workspaces.index'),
                'link_label' => 'View Workspaces',
            ];
        }

        $noManager = Workspace::where('status', 'active')->whereNull('primary_manager_id')->count();
        if ($noManager > 0) {
            $alerts[] = [
                'label'     => 'Active Workspaces Without Manager',
                'count'     => $noManager,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-user-minus',
                'link'      => route('filament.admin.resources.workspaces.index'),
                'link_label' => 'View Workspaces',
            ];
        }

        $noTalent = Workspace::where('status', 'active')->whereNull('primary_talent_id')->count();
        if ($noTalent > 0) {
            $alerts[] = [
                'label'     => 'Active Workspaces Without Talent',
                'count'     => $noTalent,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-user-minus',
                'link'      => route('filament.admin.resources.workspaces.index'),
                'link_label' => 'View Workspaces',
            ];
        }

        $failedEmails = EmailDeliveryLog::whereDate('created_at', today())
            ->where('status', 'failed')
            ->count();
        if ($failedEmails > 0) {
            $alerts[] = [
                'label'     => 'Failed Email Deliveries Today',
                'count'     => $failedEmails,
                'severity'  => 'warning',
                'icon'      => 'heroicon-o-exclamation-circle',
                'link'      => route('filament.admin.resources.email-delivery-logs.index'),
                'link_label' => 'View Logs',
            ];
        }

        return $alerts;
    }
}
