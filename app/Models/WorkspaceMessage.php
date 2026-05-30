<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'parent_id',
        'message',
        'visibility',
        'message_type',
        'edited_at',
    ];

    protected $casts = [
        'workspace_id' => 'integer',
        'user_id'      => 'integer',
        'parent_id'    => 'integer',
        'edited_at'    => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isInternal(): bool
    {
        return $this->visibility === 'internal';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isSystemMessage(): bool
    {
        return $this->message_type === 'system';
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The parent message this is a reply to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct replies to this message.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }
}
