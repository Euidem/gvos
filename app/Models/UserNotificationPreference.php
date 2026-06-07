<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_key',
        'in_app_enabled',
        'email_enabled',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'in_app_enabled' => 'boolean',
        'email_enabled' => 'boolean',
    ];

    public const DEFINITIONS = [
        'task_assigned' => [
            'label' => 'Task assigned',
            'description' => 'A task is assigned to you.',
            'email_default' => true,
        ],
        'task_status_changed' => [
            'label' => 'Task status changed',
            'description' => 'A task moves to a new workflow status.',
            'email_default' => false,
        ],
        'task_comment_added' => [
            'label' => 'Task comment added',
            'description' => 'A public or internal task comment is added where you have access.',
            'email_default' => false,
        ],
        'file_uploaded' => [
            'label' => 'File uploaded',
            'description' => 'A file is uploaded to a workspace or task you can access.',
            'email_default' => false,
        ],
        'workspace_message' => [
            'label' => 'Workspace message',
            'description' => 'A workspace chat message is posted where you have access.',
            'email_default' => false,
        ],
        'time_log_submitted' => [
            'label' => 'Time log submitted',
            'description' => 'A work session is submitted for review.',
            'email_default' => true,
        ],
        'weekly_report_published' => [
            'label' => 'Weekly report published',
            'description' => 'A weekly report is published to client-visible workspace reports.',
            'email_default' => true,
        ],
        'invoice_issued' => [
            'label' => 'Invoice issued',
            'description' => 'An invoice is issued for a workspace you can access.',
            'email_default' => true,
        ],
        'payment_recorded' => [
            'label' => 'Payment recorded',
            'description' => 'A payment is recorded or confirmed for a workspace invoice.',
            'email_default' => true,
        ],
        'trial_approved' => [
            'label' => 'Trial approved',
            'description' => 'A service trial is approved for your account.',
            'email_default' => true,
        ],
        'workspace_member_added' => [
            'label' => 'Workspace access added',
            'description' => 'You are added to a workspace team.',
            'email_default' => true,
        ],
        'workspace_role_changed' => [
            'label' => 'Workspace role changed',
            'description' => 'Your workspace role is updated.',
            'email_default' => true,
        ],
        'workspace_member_deactivated' => [
            'label' => 'Workspace access removed',
            'description' => 'Your workspace access is deactivated.',
            'email_default' => true,
        ],
        'workspace_invitation_sent' => [
            'label' => 'Workspace invitation sent',
            'description' => 'You receive a workspace invitation.',
            'email_default' => false,
        ],
        'workspace_invitation_accepted' => [
            'label' => 'Workspace invitation accepted',
            'description' => 'A workspace invitation you sent or manage is accepted.',
            'email_default' => true,
        ],
    ];

    public static function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    public static function definition(string $key): array
    {
        return self::DEFINITIONS[$key] ?? [
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'description' => 'GVOS notification.',
            'email_default' => false,
        ];
    }

    public static function defaultInAppEnabled(string $key): bool
    {
        return array_key_exists($key, self::DEFINITIONS);
    }

    public static function defaultEmailEnabled(string $key): bool
    {
        return (bool) (self::definition($key)['email_default'] ?? false);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
