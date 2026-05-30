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
}
