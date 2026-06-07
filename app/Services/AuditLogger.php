<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an auditable action.
     *
     * @param  string       $action   Dot-namespaced action: user.created, user.role_changed …
     * @param  Model|null   $subject  The model that was acted upon.
     * @param  array        $context  Extra key→value pairs (from/to values, metadata).
     * @param  int|null     $actorId  Override the actor; defaults to the authenticated user.
     */
    public static function log(
        string  $action,
        ?Model  $subject = null,
        array   $context = [],
        ?int    $actorId = null,
    ): void {
        try {
            /** @var Request $request */
            $request = app('request');

            AuditLog::create([
                'user_id'      => $actorId ?? Auth::id(),
                'action'       => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'context'      => array_merge(
                    ['_actor_name' => Auth::user()?->name ?? 'system'],
                    $context,
                ),
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the main request.
            // In production, consider logging to the error log here.
            \Illuminate\Support\Facades\Log::warning(
                'AuditLogger::log failed: ' . $e->getMessage(),
                ['action' => $action]
            );
        }
    }

    // ── Convenience wrappers ─────────────────────────────────────────────

    public static function userCreated(Model $user, array $extra = []): void
    {
        self::log('user.created', $user, array_merge(['email' => $user->email ?? null], $extra));
    }

    public static function userUpdated(Model $user, array $changes = []): void
    {
        self::log('user.updated', $user, $changes);
    }

    public static function roleChanged(Model $user, string $from, string $to): void
    {
        self::log('user.role_changed', $user, ['from' => $from, 'to' => $to]);
    }

    public static function statusChanged(Model $user, string $from, string $to): void
    {
        self::log('user.status_changed', $user, ['from' => $from, 'to' => $to]);
    }

    public static function passwordChanged(Model $user): void
    {
        self::log('user.password_changed', $user);
    }

    public static function profileUpdated(Model $user, array $changes = []): void
    {
        self::log('user.profile_updated', $user, $changes);
    }

    public static function notificationPreferencesUpdated(Model $user, array $changes = []): void
    {
        self::log('notification_preferences.updated', $user, $changes);
    }

    public static function login(Model $user): void
    {
        self::log('user.login', $user, ['email' => $user->email ?? null]);
    }

    // ── Phase 2 — People & Organizations ─────────────────────────────────

    public static function companyCreated(Model $company, array $extra = []): void
    {
        self::log('company.created', $company, array_merge(['name' => $company->name ?? null], $extra));
    }

    public static function companyUpdated(Model $company, array $changes = []): void
    {
        self::log('company.updated', $company, $changes);
    }

    public static function departmentCreated(Model $department, array $extra = []): void
    {
        self::log('department.created', $department, array_merge(['name' => $department->name ?? null], $extra));
    }

    public static function departmentUpdated(Model $department, array $changes = []): void
    {
        self::log('department.updated', $department, $changes);
    }

    public static function clientProfileCreated(Model $clientProfile, array $extra = []): void
    {
        self::log('client_profile.created', $clientProfile, array_merge(['user_id' => $clientProfile->user_id ?? null], $extra));
    }

    public static function clientProfileUpdated(Model $clientProfile, array $changes = []): void
    {
        self::log('client_profile.updated', $clientProfile, $changes);
    }

    public static function talentProfileCreated(Model $talentProfile, array $extra = []): void
    {
        self::log('talent_profile.created', $talentProfile, array_merge(['user_id' => $talentProfile->user_id ?? null], $extra));
    }

    public static function talentProfileUpdated(Model $talentProfile, array $changes = []): void
    {
        self::log('talent_profile.updated', $talentProfile, $changes);
    }

    public static function managerProfileCreated(Model $managerProfile, array $extra = []): void
    {
        self::log('manager_profile.created', $managerProfile, array_merge(['user_id' => $managerProfile->user_id ?? null], $extra));
    }

    public static function managerProfileUpdated(Model $managerProfile, array $changes = []): void
    {
        self::log('manager_profile.updated', $managerProfile, $changes);
    }

    // ── Phase 3 — Leads & Trial Flow ─────────────────────────────────────

    public static function leadRequestCreated(Model $leadRequest, array $extra = []): void
    {
        self::log('lead_request.created', $leadRequest, array_merge([
            'email'       => $leadRequest->email ?? null,
            'client_type' => $leadRequest->client_type ?? null,
        ], $extra));
    }

    public static function leadRequestUpdated(Model $leadRequest, array $changes = []): void
    {
        self::log('lead_request.updated', $leadRequest, $changes);
    }

    public static function leadRequestStatusChanged(Model $leadRequest, string $from, string $to): void
    {
        self::log('lead_request.status_changed', $leadRequest, ['from' => $from, 'to' => $to]);
    }

    public static function priceEstimateCreated(Model $estimate, array $extra = []): void
    {
        self::log('price_estimate.created', $estimate, array_merge([
            'lead_request_id' => $estimate->lead_request_id ?? null,
            'currency'        => $estimate->currency ?? null,
            'amount'          => $estimate->estimated_amount ?? null,
        ], $extra));
    }

    public static function priceEstimateUpdated(Model $estimate, array $changes = []): void
    {
        self::log('price_estimate.updated', $estimate, $changes);
    }

    public static function priceEstimateAccepted(Model $estimate, array $extra = []): void
    {
        self::log('price_estimate.accepted', $estimate, array_merge([
            'lead_request_id' => $estimate->lead_request_id ?? null,
            'accepted_at'     => now()->toDateTimeString(),
        ], $extra));
    }

    public static function trialCreated(Model $trial, array $extra = []): void
    {
        self::log('trial.created', $trial, array_merge([
            'lead_request_id' => $trial->lead_request_id ?? null,
            'status'          => $trial->status ?? null,
        ], $extra));
    }

    public static function trialUpdated(Model $trial, array $changes = []): void
    {
        self::log('trial.updated', $trial, $changes);
    }

    public static function trialStarted(Model $trial, array $extra = []): void
    {
        self::log('trial.started', $trial, array_merge([
            'starts_at' => $trial->starts_at ?? null,
            'ends_at'   => $trial->ends_at ?? null,
        ], $extra));
    }

    public static function trialCompleted(Model $trial, array $extra = []): void
    {
        self::log('trial.completed', $trial, array_merge([
            'completed_at' => now()->toDateTimeString(),
        ], $extra));
    }

    public static function trialCancelled(Model $trial, array $extra = []): void
    {
        self::log('trial.cancelled', $trial, array_merge([
            'cancelled_at' => now()->toDateTimeString(),
        ], $extra));
    }

    public static function trialPaymentPending(Model $trial, array $extra = []): void
    {
        self::log('trial.payment_pending', $trial, array_merge([
            'lead_request_id' => $trial->lead_request_id ?? null,
        ], $extra));
    }

    // ── Phase 4 — Workspace Engine ────────────────────────────────────────

    public static function workspaceCreated(Model $workspace, array $extra = []): void
    {
        self::log('workspace.created', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'name'           => $workspace->name ?? null,
            'type'           => $workspace->type ?? null,
        ], $extra));
    }

    public static function workspaceUpdated(Model $workspace, array $changes = []): void
    {
        self::log('workspace.updated', $workspace, $changes);
    }

    public static function workspaceStatusChanged(Model $workspace, string $from, string $to): void
    {
        self::log('workspace.status_changed', $workspace, [
            'workspace_code' => $workspace->workspace_code ?? null,
            'from'           => $from,
            'to'             => $to,
        ]);
    }

    public static function workspaceMemberAdded(Model $workspace, Model $member, array $extra = []): void
    {
        self::log('workspace.member_added', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'user_id'        => $member->user_id ?? null,
            'role'           => $member->role ?? null,
        ], $extra));
    }

    public static function workspaceMemberUpdated(Model $workspace, Model $member, array $extra = []): void
    {
        self::log('workspace.member_updated', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'user_id'        => $member->user_id ?? null,
        ], $extra));
    }

    public static function workspaceMemberRemoved(Model $workspace, Model $member, array $extra = []): void
    {
        self::log('workspace.member_removed', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'user_id'        => $member->user_id ?? null,
            'removed_at'     => now()->toDateTimeString(),
        ], $extra));
    }

    public static function workspaceMembershipAdded(Model $workspace, Model $member, array $extra = []): void
    {
        self::log('workspace_member.added', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'member_id'      => $member->id ?? null,
            'user_id'        => $member->user_id ?? null,
            'role'           => $member->role ?? null,
        ], $extra));
    }

    public static function workspaceMemberRoleChanged(Model $workspace, Model $member, string $from, string $to, array $extra = []): void
    {
        self::log('workspace_member.role_changed', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'member_id'      => $member->id ?? null,
            'user_id'        => $member->user_id ?? null,
            'from'           => $from,
            'to'             => $to,
        ], $extra));
    }

    public static function workspaceMemberDeactivated(Model $workspace, Model $member, array $extra = []): void
    {
        self::log('workspace_member.deactivated', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'member_id'      => $member->id ?? null,
            'user_id'        => $member->user_id ?? null,
            'role'           => $member->role ?? null,
            'deactivated_at' => now()->toDateTimeString(),
        ], $extra));
    }

    public static function workspaceInvitationCreated(Model $invitation, array $extra = []): void
    {
        self::workspaceInvitationEvent('workspace_invitation.created', $invitation, $extra);
    }

    public static function workspaceInvitationResent(Model $invitation, array $extra = []): void
    {
        self::workspaceInvitationEvent('workspace_invitation.resent', $invitation, $extra);
    }

    public static function workspaceInvitationRevoked(Model $invitation, array $extra = []): void
    {
        self::workspaceInvitationEvent('workspace_invitation.revoked', $invitation, $extra);
    }

    public static function workspaceInvitationAccepted(Model $invitation, array $extra = []): void
    {
        self::workspaceInvitationEvent('workspace_invitation.accepted', $invitation, $extra);
    }

    private static function workspaceInvitationEvent(string $action, Model $invitation, array $extra = []): void
    {
        self::log($action, $invitation, array_merge([
            'workspace_id'    => $invitation->workspace_id ?? null,
            'invitation_id'   => $invitation->id ?? null,
            'email_hash'      => isset($invitation->email) ? hash('sha256', strtolower($invitation->email)) : null,
            'workspace_role'  => $invitation->workspace_role ?? null,
            'status'          => $invitation->status ?? null,
        ], $extra));
    }

    public static function workspacePrimaryTeamSynced(Model $workspace, array $syncResult = []): void
    {
        self::log('workspace.primary_team_synced', $workspace, array_merge([
            'workspace_code' => $workspace->workspace_code ?? null,
            'added'          => count($syncResult['added'] ?? []),
            'reactivated'    => count($syncResult['reactivated'] ?? []),
            'skipped'        => count($syncResult['skipped'] ?? []),
        ], $syncResult));
    }

    public static function trialWorkspaceCreated(Model $trial, Model $workspace, array $extra = []): void
    {
        self::log('trial.workspace_created', $trial, array_merge([
            'trial_code'     => $trial->trial_code ?? null,
            'workspace_code' => $workspace->workspace_code ?? null,
            'workspace_id'   => $workspace->id ?? null,
        ], $extra));
    }

    // ── Phase 5 — Workspace Tasks ─────────────────────────────────────────

    public static function workspaceTaskCreated(Model $task, array $extra = []): void
    {
        self::log('workspace_task.created', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
            'title'        => $task->title ?? null,
        ], $extra));
    }

    public static function workspaceTaskUpdated(Model $task, array $changes = []): void
    {
        self::log('workspace_task.updated', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
        ], $changes));
    }

    public static function workspaceTaskStatusChanged(Model $task, string $from, string $to, array $extra = []): void
    {
        self::log('workspace_task.status_changed', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
            'from'         => $from,
            'to'           => $to,
        ], $extra));
    }

    public static function workspaceTaskAssigned(Model $task, array $extra = []): void
    {
        self::log('workspace_task.assigned', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
        ], $extra));
    }

    public static function workspaceTaskCommentAdded(Model $task, array $extra = []): void
    {
        self::log('workspace_task.comment_added', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
        ], $extra));
    }

    public static function workspaceTaskInternalCommentAdded(Model $task, array $extra = []): void
    {
        self::log('workspace_task.internal_comment_added', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
        ], $extra));
    }

    public static function workspaceTaskDeleted(Model $task, array $extra = []): void
    {
        self::log('workspace_task.deleted', $task, array_merge([
            'task_code'    => $task->task_code ?? null,
            'workspace_id' => $task->workspace_id ?? null,
            'title'        => $task->title ?? null,
        ], $extra));
    }

    // ── Phase 6 — Workspace Chat & Files ─────────────────────────────────

    public static function workspaceMessageCreated(Model $message, array $extra = []): void
    {
        self::log('workspace_message.created', $message, array_merge([
            'workspace_id' => $message->workspace_id ?? null,
            'visibility'   => $message->visibility ?? null,
        ], $extra));
    }

    public static function workspaceMessageUpdated(Model $message, array $extra = []): void
    {
        self::log('workspace_message.updated', $message, array_merge([
            'workspace_id' => $message->workspace_id ?? null,
            'visibility'   => $message->visibility ?? null,
        ], $extra));
    }

    public static function workspaceMessageDeleted(Model $message, array $extra = []): void
    {
        self::log('workspace_message.deleted', $message, array_merge([
            'workspace_id' => $message->workspace_id ?? null,
            'visibility'   => $message->visibility ?? null,
        ], $extra));
    }

    public static function workspaceFileUploaded(Model $file, array $extra = []): void
    {
        self::log('workspace_file.uploaded', $file, array_merge([
            'workspace_id'        => $file->workspace_id ?? null,
            'original_filename'   => $file->original_filename ?? null,
            'visibility'          => $file->visibility ?? null,
            'category'            => $file->category ?? null,
            'workspace_task_id'   => $file->workspace_task_id ?? null,
            'uploaded_by_user_id' => $file->uploaded_by_user_id ?? null,
        ], $extra));
    }

    public static function workspaceFileDownloaded(Model $file, array $extra = []): void
    {
        self::log('workspace_file.downloaded', $file, array_merge([
            'workspace_id'      => $file->workspace_id ?? null,
            'original_filename' => $file->original_filename ?? null,
            'visibility'        => $file->visibility ?? null,
        ], $extra));
    }

    public static function workspaceFileDeleted(Model $file, array $extra = []): void
    {
        self::log('workspace_file.deleted', $file, array_merge([
            'workspace_id'      => $file->workspace_id ?? null,
            'original_filename' => $file->original_filename ?? null,
            'visibility'        => $file->visibility ?? null,
        ], $extra));
    }

    // ── Phase 7: Time Log wrappers ────────────────────────────────────────

    public static function timeLogCreated(Model $timeLog, array $extra = []): void
    {
        self::log('time_log.created', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'log_date'     => $timeLog->log_date ?? null,
            'status'       => $timeLog->status ?? null,
        ], $extra));
    }

    public static function timeLogUpdated(Model $timeLog, array $extra = []): void
    {
        self::log('time_log.updated', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'status'       => $timeLog->status ?? null,
        ], $extra));
    }

    public static function timeLogReviewed(Model $timeLog, array $extra = []): void
    {
        self::log('time_log.reviewed', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'status'       => $timeLog->status ?? null,
            'visibility'   => $timeLog->visibility ?? null,
        ], $extra));
    }

    public static function timeLogDeleted(Model $timeLog, array $extra = []): void
    {
        self::log('time_log.deleted', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'log_date'     => $timeLog->log_date ?? null,
        ], $extra));
    }

    // ── Phase 9: Semi-automated time tracker wrappers ─────────────────────

    public static function timeTrackerStarted(Model $timeLog, array $extra = []): void
    {
        self::log('workspace_time_tracker.started', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'time_log_id'  => $timeLog->id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'task_id'      => $timeLog->workspace_task_id ?? null,
            'started_at'   => $timeLog->started_at?->toDateTimeString(),
            'ended_at'     => $timeLog->ended_at?->toDateTimeString(),
            'duration_minutes' => $timeLog->duration_minutes ?? null,
        ], $extra));
    }

    public static function timeTrackerStopped(Model $timeLog, array $extra = []): void
    {
        self::log('workspace_time_tracker.stopped', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'time_log_id'  => $timeLog->id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'task_id'      => $timeLog->workspace_task_id ?? null,
            'started_at'   => $timeLog->started_at?->toDateTimeString(),
            'ended_at'     => $timeLog->ended_at?->toDateTimeString(),
            'duration_minutes' => $timeLog->duration_minutes ?? null,
        ], $extra));
    }

    public static function timeTrackerCompleted(Model $timeLog, array $extra = []): void
    {
        self::log('workspace_time_tracker.completed', $timeLog, array_merge([
            'workspace_id' => $timeLog->workspace_id ?? null,
            'time_log_id'  => $timeLog->id ?? null,
            'user_id'      => $timeLog->user_id ?? null,
            'task_id'      => $timeLog->workspace_task_id ?? null,
            'started_at'   => $timeLog->started_at?->toDateTimeString(),
            'ended_at'     => $timeLog->ended_at?->toDateTimeString(),
            'duration_minutes' => $timeLog->duration_minutes ?? null,
        ], $extra));
    }

    // ── Phase 7: Weekly Report wrappers ───────────────────────────────────

    public static function weeklyReportCreated(Model $report, array $extra = []): void
    {
        self::log('weekly_report.created', $report, array_merge([
            'workspace_id'    => $report->workspace_id ?? null,
            'week_start_date' => $report->week_start_date ?? null,
            'week_end_date'   => $report->week_end_date ?? null,
            'status'          => $report->status ?? null,
        ], $extra));
    }

    public static function weeklyReportUpdated(Model $report, array $extra = []): void
    {
        self::log('weekly_report.updated', $report, array_merge([
            'workspace_id' => $report->workspace_id ?? null,
            'status'       => $report->status ?? null,
        ], $extra));
    }

    public static function weeklyReportDeleted(Model $report, array $extra = []): void
    {
        self::log('weekly_report.deleted', $report, array_merge([
            'workspace_id'    => $report->workspace_id ?? null,
            'week_start_date' => $report->week_start_date ?? null,
            'status'          => $report->status ?? null,
        ], $extra));
    }

    public static function weeklyReportPublished(Model $report, array $extra = []): void
    {
        self::log('weekly_report.published', $report, array_merge([
            'workspace_id'    => $report->workspace_id ?? null,
            'week_start_date' => $report->week_start_date ?? null,
        ], $extra));
    }

    public static function weeklyReportStatusChanged(Model $report, string $from, string $to, array $extra = []): void
    {
        self::log('weekly_report.status_changed', $report, array_merge([
            'workspace_id' => $report->workspace_id ?? null,
            'from'         => $from,
            'to'           => $to,
        ], $extra));
    }

    // ── Phase 8: Billing wrappers ─────────────────────────────────────────

    public static function billingPlanCreated(Model $plan, array $extra = []): void
    {
        self::log('billing_plan.created', $plan, array_merge([
            'name'     => $plan->name ?? null,
            'currency' => $plan->currency ?? null,
            'amount'   => $plan->amount ?? null,
        ], $extra));
    }

    public static function billingPlanUpdated(Model $plan, array $extra = []): void
    {
        self::log('billing_plan.updated', $plan, array_merge([
            'name'   => $plan->name ?? null,
            'status' => $plan->status ?? null,
        ], $extra));
    }

    public static function subscriptionCreated(Model $sub, array $extra = []): void
    {
        self::log('workspace_subscription.created', $sub, array_merge([
            'workspace_id' => $sub->workspace_id ?? null,
            'amount'       => $sub->amount ?? null,
            'currency'     => $sub->currency ?? null,
            'status'       => $sub->status ?? null,
        ], $extra));
    }

    public static function subscriptionUpdated(Model $sub, array $extra = []): void
    {
        self::log('workspace_subscription.updated', $sub, array_merge([
            'workspace_id' => $sub->workspace_id ?? null,
            'status'       => $sub->status ?? null,
        ], $extra));
    }

    public static function invoiceCreated(Model $invoice, array $extra = []): void
    {
        self::log('invoice.created', $invoice, array_merge([
            'invoice_number' => $invoice->invoice_number ?? null,
            'workspace_id'   => $invoice->workspace_id ?? null,
            'total_amount'   => $invoice->total_amount ?? null,
            'currency'       => $invoice->currency ?? null,
        ], $extra));
    }

    public static function invoiceUpdated(Model $invoice, array $extra = []): void
    {
        self::log('invoice.updated', $invoice, array_merge([
            'invoice_number' => $invoice->invoice_number ?? null,
            'status'         => $invoice->status ?? null,
        ], $extra));
    }

    public static function invoiceIssued(Model $invoice, array $extra = []): void
    {
        self::log('invoice.issued', $invoice, array_merge([
            'invoice_number' => $invoice->invoice_number ?? null,
            'workspace_id'   => $invoice->workspace_id ?? null,
            'total_amount'   => $invoice->total_amount ?? null,
        ], $extra));
    }

    public static function invoiceCancelled(Model $invoice, array $extra = []): void
    {
        self::log('invoice.cancelled', $invoice, array_merge([
            'invoice_number' => $invoice->invoice_number ?? null,
            'workspace_id'   => $invoice->workspace_id ?? null,
        ], $extra));
    }

    public static function invoiceMarkedPaid(Model $invoice, array $extra = []): void
    {
        self::log('invoice.marked_paid', $invoice, array_merge([
            'invoice_number' => $invoice->invoice_number ?? null,
            'workspace_id'   => $invoice->workspace_id ?? null,
            'total_amount'   => $invoice->total_amount ?? null,
        ], $extra));
    }

    public static function paymentRecorded(Model $payment, array $extra = []): void
    {
        self::log('payment.recorded', $payment, array_merge([
            'payment_reference' => $payment->payment_reference ?? null,
            'workspace_id'      => $payment->workspace_id ?? null,
            'amount'            => $payment->amount ?? null,
            'currency'          => $payment->currency ?? null,
            'provider'          => $payment->provider ?? null,
        ], $extra));
    }

    public static function paymentConfirmed(Model $payment, array $extra = []): void
    {
        self::log('payment.confirmed', $payment, array_merge([
            'payment_reference' => $payment->payment_reference ?? null,
            'workspace_id'      => $payment->workspace_id ?? null,
            'amount'            => $payment->amount ?? null,
        ], $extra));
    }

    public static function paymentFailedOrCancelled(Model $payment, array $extra = []): void
    {
        self::log('payment.failed_or_cancelled', $payment, array_merge([
            'payment_reference' => $payment->payment_reference ?? null,
            'status'            => $payment->status ?? null,
        ], $extra));
    }

    // ── Phase 10: Password Vault wrappers ───────────────────────────────────

    private static function vaultContext(Model $vaultItem, array $extra = []): array
    {
        return array_merge([
            'workspace_id' => $vaultItem->workspace_id ?? null,
            'vault_item_id' => $vaultItem->id ?? null,
            'title' => $vaultItem->title ?? null,
            'visibility' => $vaultItem->visibility ?? null,
            'status' => $vaultItem->status ?? null,
        ], $extra);
    }

    public static function workspaceVaultItemCreated(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.created', $vaultItem, self::vaultContext($vaultItem, $extra));
    }

    public static function workspaceVaultItemUpdated(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.updated', $vaultItem, self::vaultContext($vaultItem, $extra));
    }

    public static function workspaceVaultItemArchived(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.archived', $vaultItem, self::vaultContext($vaultItem, $extra));
    }

    public static function workspaceVaultItemRestored(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.restored', $vaultItem, self::vaultContext($vaultItem, $extra));
    }

    public static function workspaceVaultSecretRevealed(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.secret_revealed', $vaultItem, self::vaultContext($vaultItem, $extra));
    }

    public static function workspaceVaultAccessLogsViewed(Model $vaultItem, array $extra = []): void
    {
        self::log('workspace_vault_item.access_logs_viewed', $vaultItem, self::vaultContext($vaultItem, $extra));
    }
}
