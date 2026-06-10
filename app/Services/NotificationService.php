<?php

namespace App\Services;

use App\Models\EmailDeliveryLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Trial;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskComment;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceWeeklyReport;
use App\Notifications\GvosNotification;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\PaymentRecordedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentAddedNotification;
use App\Notifications\TaskStatusChangedNotification;
use App\Notifications\TimeLogSubmittedNotification;
use App\Notifications\TrialApprovedNotification;
use App\Notifications\WeeklyReportPublishedNotification;
use App\Notifications\WorkspaceFileUploadedNotification;
use App\Notifications\WorkspaceInvitationAcceptedNotification;
use App\Notifications\WorkspaceInvitationMailNotification;
use App\Notifications\WorkspaceInvitationSentNotification;
use App\Notifications\WorkspaceMemberAddedNotification;
use App\Notifications\WorkspaceMemberDeactivatedNotification;
use App\Notifications\WorkspaceMemberRoleChangedNotification;
use App\Notifications\WorkspaceMessageNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyTaskAssigned(WorkspaceTask $task, ?User $actor = null): void
    {
        $task->loadMissing(['assignedTo', 'workspace']);

        if (! $task->assignedTo || ! $task->workspace) {
            return;
        }

        $this->send(
            collect([$task->assignedTo]),
            'task_assigned',
            new TaskAssignedNotification($task),
            $actor
        );
    }

    public function notifyTaskStatusChanged(
        WorkspaceTask $task,
        string $oldStatus,
        string $newStatus,
        ?User $actor = null
    ): void {
        $task->loadMissing(['workspace', 'createdBy', 'assignedTo']);

        if (! $task->workspace || $oldStatus === $newStatus) {
            return;
        }

        $recipients = $this->workspaceInternalRecipients($task->workspace)
            ->merge([$task->createdBy, $task->assignedTo]);

        if (in_array($newStatus, ['submitted', 'approved', 'revision_requested', 'closed'], true)) {
            $recipients = $recipients->merge($this->workspaceClientRecipients($task->workspace));
        }

        $this->send(
            $this->filterWorkspaceRecipients($task->workspace, $recipients),
            'task_status_changed',
            new TaskStatusChangedNotification($task, $oldStatus, $newStatus),
            $actor
        );
    }

    public function notifyTaskCommentAdded(WorkspaceTaskComment $comment, ?User $actor = null): void
    {
        $comment->loadMissing(['task.workspace', 'task.createdBy', 'task.assignedTo']);

        $task = $comment->task;
        $workspace = $task?->workspace;

        if (! $task || ! $workspace) {
            return;
        }

        $recipients = $this->workspaceInternalRecipients($workspace);

        if ($comment->isPublic()) {
            $recipients = $recipients->merge([$task->createdBy, $task->assignedTo]);
        }

        $this->send(
            $this->filterWorkspaceRecipients($workspace, $recipients),
            'task_comment_added',
            new TaskCommentAddedNotification($comment),
            $actor
        );
    }

    public function notifyFileUploaded(WorkspaceFile $file, ?User $actor = null): void
    {
        $file->loadMissing(['workspace', 'task.assignedTo']);

        if (! $file->workspace) {
            return;
        }

        $recipients = $this->workspaceInternalRecipients($file->workspace);

        if ($file->isPublic() && $file->task?->assignedTo) {
            $recipients = $recipients->push($file->task->assignedTo);
        }

        $this->send(
            $this->filterWorkspaceRecipients($file->workspace, $recipients),
            'file_uploaded',
            new WorkspaceFileUploadedNotification($file),
            $actor
        );
    }

    public function notifyWorkspaceMessage(WorkspaceMessage $message, ?User $actor = null): void
    {
        $message->loadMissing('workspace');

        if (! $message->workspace) {
            return;
        }

        $recipients = $message->isInternal()
            ? $this->workspaceInternalRecipients($message->workspace)
            : $this->workspaceMembers($message->workspace);

        $this->send(
            $this->filterWorkspaceRecipients($message->workspace, $recipients),
            'workspace_message',
            new WorkspaceMessageNotification($message),
            $actor
        );
    }

    public function notifyTimeLogSubmitted(WorkspaceTimeLog $timeLog, ?User $actor = null): void
    {
        $timeLog->loadMissing('workspace');

        if (! $timeLog->workspace || $timeLog->status !== 'submitted') {
            return;
        }

        $this->send(
            $this->workspaceInternalRecipients($timeLog->workspace),
            'time_log_submitted',
            new TimeLogSubmittedNotification($timeLog),
            $actor
        );
    }

    public function notifyWeeklyReportPublished(WorkspaceWeeklyReport $report, ?User $actor = null): void
    {
        $report->loadMissing('workspace');

        if (! $report->workspace || $report->status !== 'published') {
            return;
        }

        $this->send(
            $this->workspaceClientRecipients($report->workspace),
            'weekly_report_published',
            new WeeklyReportPublishedNotification($report),
            $actor
        );
    }

    public function notifyInvoiceIssued(Invoice $invoice, ?User $actor = null): void
    {
        $invoice->loadMissing('workspace');

        if (! $invoice->workspace || ! $invoice->isClientVisible()) {
            return;
        }

        $this->send(
            $this->workspaceClientRecipients($invoice->workspace),
            'invoice_issued',
            new InvoiceIssuedNotification($invoice),
            $actor
        );
    }

    public function notifyPaymentRecorded(Payment $payment, ?User $actor = null): void
    {
        $payment->loadMissing('workspace');

        $recipients = $payment->workspace
            ? $this->workspaceClientRecipients($payment->workspace)->merge($this->systemAdmins())
            : $this->systemAdmins();

        $this->send(
            $recipients,
            'payment_recorded',
            new PaymentRecordedNotification($payment),
            $actor
        );
    }

    public function notifyTrialApproved(Trial $trial, ?User $actor = null): void
    {
        $trial->loadMissing('activeLeadUser');

        if (! $trial->activeLeadUser) {
            return;
        }

        $this->send(
            collect([$trial->activeLeadUser]),
            'trial_approved',
            new TrialApprovedNotification($trial),
            $actor
        );
    }

    public function notifyWorkspaceMemberAdded(WorkspaceMember $member, ?User $actor = null): void
    {
        $member->loadMissing(['user', 'workspace']);

        if (! $member->user || ! $member->workspace) {
            return;
        }

        $this->send(
            collect([$member->user]),
            'workspace_member_added',
            new WorkspaceMemberAddedNotification($member),
            $actor,
            false
        );
    }

    public function notifyWorkspaceMemberRoleChanged(WorkspaceMember $member, string $oldRole, ?User $actor = null): void
    {
        $member->loadMissing(['user', 'workspace']);

        if (! $member->user || ! $member->workspace) {
            return;
        }

        $this->send(
            collect([$member->user]),
            'workspace_role_changed',
            new WorkspaceMemberRoleChangedNotification($member, $oldRole),
            $actor,
            false
        );
    }

    public function notifyWorkspaceMemberDeactivated(WorkspaceMember $member, ?User $actor = null): void
    {
        $member->loadMissing(['user', 'workspace']);

        if (! $member->user || ! $member->workspace) {
            return;
        }

        $this->send(
            collect([$member->user]),
            'workspace_member_deactivated',
            new WorkspaceMemberDeactivatedNotification($member),
            $actor,
            false
        );
    }

    public function notifyWorkspaceInvitationSent(WorkspaceInvitation $invitation, ?User $actor = null): void
    {
        $invitation->loadMissing('workspace');

        $existingUser = User::query()
            ->where('email', $invitation->email)
            ->first();

        if ($existingUser) {
            $this->send(
                collect([$existingUser]),
                'workspace_invitation_sent',
                new WorkspaceInvitationSentNotification($invitation),
                $actor,
                false
            );
        }

        $this->mailInvitationSafely($invitation);
    }

    public function notifyWorkspaceInvitationAccepted(WorkspaceInvitation $invitation, ?User $actor = null): void
    {
        $invitation->loadMissing(['workspace', 'inviter', 'acceptedBy']);

        if (! $invitation->workspace) {
            return;
        }

        $recipients = $this->workspaceInternalRecipients($invitation->workspace)
            ->merge([$invitation->inviter]);

        $this->send(
            $this->filterWorkspaceRecipients($invitation->workspace, $recipients),
            'workspace_invitation_accepted',
            new WorkspaceInvitationAcceptedNotification($invitation),
            $actor
        );
    }

    private function send(
        iterable $recipients,
        string $key,
        GvosNotification $notification,
        ?User $actor = null,
        bool $excludeActor = true
    ): void {
        $users = collect($recipients)
            ->filter(fn ($user): bool => $user instanceof User)
            ->filter(fn (User $user): bool => ! in_array($user->status, ['suspended', 'inactive'], true))
            ->when($excludeActor && $actor, fn (Collection $users): Collection =>
                $users->reject(fn (User $user): bool => (int) $user->id === (int) $actor->id)
            )
            ->unique(fn (User $user): int => (int) $user->id)
            ->values();

        foreach ($users as $user) {
            $preferences = $this->preferencesFor($user, $key);

            if ($preferences['in_app_enabled']) {
                $this->notifySafely($user, $notification->forChannels(['database']), $key, 'database');
            }

            if ($preferences['email_enabled']) {
                $this->notifySafely($user, $notification->forChannels(['mail']), $key, 'mail');
            }
        }
    }

    private function notifySafely(User $user, GvosNotification $notification, string $key, string $channel): void
    {
        $payload = $notification->toArray(new \stdClass());
        $workspaceId = isset($payload['workspace_id']) ? (int) $payload['workspace_id'] : null;

        try {
            $user->notify($notification);

            if ($channel === 'mail') {
                EmailDeliveryLog::create([
                    'notification_key' => $key,
                    'channel' => $channel,
                    'recipient_user_id' => $user->id,
                    'recipient_email_hash' => hash('sha256', strtolower($user->email)),
                    'workspace_id' => $workspaceId,
                    'status' => 'success',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('GVOS notification delivery failed', [
                'user_id' => $user->id,
                'notification_key' => $key,
                'channel' => $channel,
                'error' => $this->sanitizeErrorMessage($e->getMessage()),
            ]);

            if ($channel === 'mail') {
                EmailDeliveryLog::create([
                    'notification_key' => $key,
                    'channel' => $channel,
                    'recipient_user_id' => $user->id,
                    'recipient_email_hash' => hash('sha256', strtolower($user->email)),
                    'workspace_id' => $workspaceId,
                    'status' => 'failed',
                    'error_message' => substr($this->sanitizeErrorMessage($e->getMessage()), 0, 255),
                ]);
            }
        }
    }

    private function mailInvitationSafely(WorkspaceInvitation $invitation): void
    {
        try {
            Notification::route('mail', $invitation->email)
                ->notify(new WorkspaceInvitationMailNotification($invitation));

            EmailDeliveryLog::create([
                'notification_key' => 'workspace_invitation_mail',
                'channel' => 'mail',
                'recipient_user_id' => null,
                'recipient_email_hash' => hash('sha256', strtolower($invitation->email)),
                'workspace_id' => $invitation->workspace_id,
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {
            Log::warning('GVOS workspace invitation mail failed', [
                'workspace_id' => $invitation->workspace_id,
                'invitation_id' => $invitation->id,
                'email_hash' => hash('sha256', strtolower($invitation->email)),
                'error' => $this->sanitizeErrorMessage($e->getMessage()),
            ]);

            EmailDeliveryLog::create([
                'notification_key' => 'workspace_invitation_mail',
                'channel' => 'mail',
                'recipient_user_id' => null,
                'recipient_email_hash' => hash('sha256', strtolower($invitation->email)),
                'workspace_id' => $invitation->workspace_id,
                'status' => 'failed',
                'error_message' => substr($this->sanitizeErrorMessage($e->getMessage()), 0, 255),
            ]);
        }
    }

    private function sanitizeErrorMessage(string $message): string
    {
        // Strip potential SMTP credentials or connection strings from error messages
        $message = preg_replace('/password[=:\s]+\S+/i', 'password=[redacted]', $message);
        $message = preg_replace('/username[=:\s]+\S+/i', 'username=[redacted]', $message);
        $message = preg_replace('/://[^@\s]+@/', '://[credentials]@', $message);

        return $message;
    }

    private function preferencesFor(User $user, string $key): array
    {
        $preference = $user->notificationPreferences()
            ->where('notification_key', $key)
            ->first();

        return [
            'in_app_enabled' => $preference
                ? (bool) $preference->in_app_enabled
                : UserNotificationPreference::defaultInAppEnabled($key),
            'email_enabled' => $preference
                ? (bool) $preference->email_enabled
                : UserNotificationPreference::defaultEmailEnabled($key),
        ];
    }

    private function workspaceInternalRecipients(Workspace $workspace): Collection
    {
        return $this->workspaceMembersByRole($workspace, ['workspace_admin', 'manager'])
            ->merge([$workspace->primaryManager]);
    }

    private function workspaceClientRecipients(Workspace $workspace): Collection
    {
        $workspace->loadMissing('clientProfile.user');

        return $this->workspaceMembersByRole($workspace, ['client_admin', 'client_staff', 'client'])
            ->merge([$workspace->clientProfile?->user])
            ->filter(fn ($user): bool => $user instanceof User)
            ->filter(fn (User $user): bool => $workspace->userHasAccess($user))
            ->values();
    }

    private function workspaceMembers(Workspace $workspace): Collection
    {
        return $workspace->activeMembers()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();
    }

    private function workspaceMembersByRole(Workspace $workspace, array $roles): Collection
    {
        return $workspace->activeMembers()
            ->with('user')
            ->whereIn('role', $roles)
            ->get()
            ->pluck('user')
            ->filter();
    }

    private function filterWorkspaceRecipients(Workspace $workspace, Collection $users): Collection
    {
        return $users
            ->filter(fn ($user): bool => $user instanceof User)
            ->filter(fn (User $user): bool => $workspace->userHasAccess($user))
            ->values();
    }

    private function systemAdmins(): Collection
    {
        return User::role(['super_admin', 'operations_admin'])
            ->whereNotIn('status', ['suspended', 'inactive'])
            ->get();
    }
}
