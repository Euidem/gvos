<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceTaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_task_id',
        'user_id',
        'comment',
        'visibility',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTask::class, 'workspace_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isInternal(): bool
    {
        return $this->visibility === 'internal';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}
