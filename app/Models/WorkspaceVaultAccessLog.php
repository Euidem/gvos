<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class WorkspaceVaultAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'workspace_vault_item_id',
        'workspace_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'workspace_vault_item_id' => 'integer',
        'workspace_id'            => 'integer',
        'user_id'                 => 'integer',
        'metadata'                => 'array',
        'created_at'              => 'datetime',
    ];

    public static function actionLabels(): array
    {
        return [
            'created'          => 'Created',
            'updated'          => 'Updated',
            'archived'         => 'Archived',
            'restored'         => 'Restored',
            'viewed_metadata'  => 'Viewed Metadata',
            'revealed_secret'  => 'Revealed Secret',
            'copied_secret'    => 'Copied Secret',
            'viewed_logs'      => 'Viewed Logs',
        ];
    }

    public function actionLabel(): string
    {
        return static::actionLabels()[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public static function record(
        WorkspaceVaultItem $item,
        ?User $user,
        string $action,
        ?Request $request = null,
        array $metadata = [],
    ): self {
        $request ??= app('request');

        return static::create([
            'workspace_vault_item_id' => $item->id,
            'workspace_id'            => $item->workspace_id,
            'user_id'                 => $user?->id,
            'action'                  => $action,
            'ip_address'              => $request?->ip(),
            'user_agent'              => $request?->userAgent(),
            'metadata'                => $metadata ?: null,
            'created_at'              => now(),
        ]);
    }

    public function vaultItem(): BelongsTo
    {
        return $this->belongsTo(WorkspaceVaultItem::class, 'workspace_vault_item_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
