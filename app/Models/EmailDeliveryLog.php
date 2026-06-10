<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDeliveryLog extends Model
{
    protected $fillable = [
        'notification_key',
        'channel',
        'recipient_user_id',
        'recipient_email_hash',
        'workspace_id',
        'status',
        'error_message',
    ];

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
