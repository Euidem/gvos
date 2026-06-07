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
        $workspaceName = $this->invitation->workspace?->name ?? 'GVOS workspace';

        return (new MailMessage)
            ->subject('GVOS workspace invitation')
            ->greeting('Hello ' . ($this->invitation->name ?: 'there'))
            ->line('You have been invited to join ' . $workspaceName . ' as ' . ucfirst(str_replace('_', ' ', $this->invitation->workspace_role)) . '.')
            ->line('This invitation expires ' . ($this->invitation->expires_at?->format('d M Y H:i') ?? 'when revoked by an admin') . '.')
            ->action('Review Invitation', route('workspace.invitations.show', $this->invitation->token))
            ->line('If you do not have a GVOS account yet, contact your workspace admin to activate your account before accepting.');
    }
}
