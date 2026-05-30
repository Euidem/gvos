<?php

namespace App\Filament\Resources\WorkspaceTaskResource\Pages;

use App\Filament\Resources\WorkspaceTaskResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkspaceTask extends EditRecord
{
    protected static string $resource = WorkspaceTaskResource::class;

    protected array $beforeSnapshot = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->beforeSnapshot = $this->record->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $after  = $this->record->fresh()->toArray();
        $before = $this->beforeSnapshot;

        $changes = [];
        foreach (['title', 'status', 'priority', 'assigned_to_user_id', 'due_date', 'description', 'internal_notes'] as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $after[$field] ?? null];
            }
        }

        // Status change
        if (isset($changes['status'])) {
            AuditLogger::workspaceTaskStatusChanged(
                $this->record,
                $changes['status']['from'] ?? 'unknown',
                $changes['status']['to'] ?? 'unknown',
                ['source' => 'filament']
            );
        }

        // Assignment change
        if (isset($changes['assigned_to_user_id'])) {
            AuditLogger::workspaceTaskAssigned($this->record, [
                'source'          => 'filament',
                'old_assignee_id' => $changes['assigned_to_user_id']['from'],
                'new_assignee_id' => $changes['assigned_to_user_id']['to'],
            ]);
        }

        if (! empty($changes)) {
            AuditLogger::workspaceTaskUpdated($this->record, array_merge($changes, ['source' => 'filament']));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(),
        ];
    }
}
