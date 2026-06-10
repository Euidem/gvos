<?php

namespace App\Notifications;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationMailNotification extends Notification
{
    use Queueable;

    public function __construct(private WorkspaceInvitation $invitation)
    {
        $this->invitation->loadMissing('workspace', 'inviter');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workspaceName = $this->invitation->workspace?->name ?? 'a GVOS workspace';
        $roleName = ucwords(str_replace('_', ' ', $this->invitation->workspace_role));
        $inviterName = $this->invitation->inviter?->name;
        $inviterLine = $inviterName
            ? $inviterName . ' has invited you to join ' . $workspaceName . ' as ' . $roleName . '.'
            : 'You have been invited to join ' . $workspaceName . ' as ' . $roleName . '.';
        $expiry = $this->invitation->expires_at
            ? 'This invitation expires on ' . $this->invitation->expires_at->format('d M Y') . '.'
            : 'This invitation remains valid until revoked.';

        return (new MailMessage)
            ->subject('You have been invited to ' . $workspaceName . ' on GVOS')
            ->greeting('Hello ' . ($this->invitation->name ?: 'there') . ',')
            ->line($inviterLine)
            ->line($expiry)
            ->action('Review Invitation', route('workspace.invitations.show', $this->invitation->token))
            ->line('If you do not have a GVOS account, you can create one directly from the invitation link.')
            ->line('If you were not expecting this invitation, you can safely ignore this email.')
            ->salutation('The GVOS Team');
    }
}
