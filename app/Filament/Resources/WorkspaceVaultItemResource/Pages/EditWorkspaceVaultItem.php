<?php

namespace App\Filament\Resources\WorkspaceVaultItemResource\Pages;

use App\Filament\Resources\WorkspaceVaultItemResource;
use App\Models\WorkspaceVaultAccessLog;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkspaceVaultItem extends EditRecord
{
    protected static string $resource = WorkspaceVaultItemResource::class;

    protected array $beforeSnapshot = [];

    protected bool $secretWasChanged = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->beforeSnapshot = $this->safeSnapshot();
        $data['secret_value'] = null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->secretWasChanged = array_key_exists('secret_value', $data) && filled($data['secret_value']);
        $data['updated_by'] = auth()->id();

        if (! $this->secretWasChanged) {
            unset($data['secret_value']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $after = $this->safeSnapshot();
        $changes = [];

        foreach (['title', 'category', 'login_url', 'username', 'notes', 'visibility', 'status', 'allowed_roles', 'allowed_user_ids'] as $field) {
            if (($this->beforeSnapshot[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[$field] = [
                    'from' => $this->beforeSnapshot[$field] ?? null,
                    'to' => $after[$field] ?? null,
                ];
            }
        }

        if ($this->secretWasChanged) {
            $changes['secret_value'] = ['rotated' => true];
        }

        $beforeStatus = $this->beforeSnapshot['status'] ?? null;
        $afterStatus = $after['status'] ?? null;
        $action = 'updated';

        if ($beforeStatus !== 'archived' && $afterStatus === 'archived') {
            $action = 'archived';
        } elseif ($beforeStatus === 'archived' && $afterStatus === 'active') {
            $action = 'restored';
        }

        WorkspaceVaultAccessLog::record($this->record, auth()->user(), $action, request(), [
            'source' => 'filament',
            'secret_rotated' => $this->secretWasChanged,
        ]);

        if ($action === 'archived') {
            AuditLogger::workspaceVaultItemArchived($this->record, ['source' => 'filament']);
        } elseif ($action === 'restored') {
            AuditLogger::workspaceVaultItemRestored($this->record, ['source' => 'filament']);
        } else {
            AuditLogger::workspaceVaultItemUpdated($this->record, [
                'source' => 'filament',
                'updated_fields' => array_keys($changes),
                'secret_rotated' => $this->secretWasChanged,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(),
        ];
    }

    private function safeSnapshot(): array
    {
        $record = $this->record->fresh();

        return [
            'title' => $record->title,
            'category' => $record->category,
            'login_url' => $record->login_url,
            'username' => $record->username,
            'notes' => $record->notes,
            'visibility' => $record->visibility,
            'status' => $record->status,
            'allowed_roles' => $record->allowedRoleValues(),
            'allowed_user_ids' => $record->allowedUserIdValues(),
        ];
    }
}
