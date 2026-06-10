<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class GvosNotification extends Notification
{
    use Queueable;

    protected string $notificationKey;

    protected array $payload;

    protected array $channels = ['database'];

    public function __construct(string $notificationKey, array $payload)
    {
        $this->notificationKey = $notificationKey;
        $this->payload = $this->safePayload($payload);
    }

    public function forChannels(array $channels): static
    {
        $clone = clone $this;
        $clone->channels = $channels;

        return $clone;
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'notification_key' => $this->notificationKey,
        ], $this->payload);
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('GVOS: ' . ($this->payload['title'] ?? 'New notification'))
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->payload['message'] ?? 'You have a new GVOS notification.');

        if (! empty($this->payload['action_url'])) {
            $mail->action('Open in GVOS', url($this->payload['action_url']));
        }

        return $mail
            ->line('To manage your notification preferences, visit your GVOS account settings.')
            ->salutation('The GVOS Team');
    }

    private function safePayload(array $payload): array
    {
        $allowed = [
            'title',
            'message',
            'action_url',
            'workspace_id',
            'related_type',
            'related_id',
            'level',
        ];

        $safe = array_intersect_key($payload, array_flip($allowed));

        foreach (['title', 'message', 'action_url', 'related_type', 'level'] as $key) {
            if (array_key_exists($key, $safe) && $safe[$key] !== null) {
                $safe[$key] = substr((string) $safe[$key], 0, $key === 'message' ? 500 : 255);
            }
        }

        foreach (['workspace_id', 'related_id'] as $key) {
            if (array_key_exists($key, $safe) && $safe[$key] !== null) {
                $safe[$key] = (int) $safe[$key];
            }
        }

        return $safe;
    }
}
