<?php

namespace App\Filament\Resources\WorkspaceResource\Pages;

use App\Filament\Resources\WorkspaceResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkspace extends EditRecord
{
    protected static string $resource = WorkspaceResource::class;

    protected array $beforeSnapshot = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Capture snapshot before any edits
        $this->beforeSnapshot = $this->record->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $after  = $this->record->fresh()->toArray();
        $before = $this->beforeSnapshot;

        $changes = [];
        foreach (['name', 'status', 'type', 'description', 'starts_at', 'ends_at', 'task_limit', 'file_limit_mb', 'notes'] as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $after[$field] ?? null];
            }
        }

        if (! empty($changes)) {
            AuditLogger::workspaceUpdated($this->record, $changes);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(),
        ];
    }
}
