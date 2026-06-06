<?php

namespace App\Filament\Resources\WorkspaceVaultItemResource\Pages;

use App\Filament\Resources\WorkspaceVaultItemResource;
use App\Models\WorkspaceVaultAccessLog;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspaceVaultItem extends CreateRecord
{
    protected static string $resource = WorkspaceVaultItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        WorkspaceVaultAccessLog::record($this->record, auth()->user(), 'created', request(), [
            'source' => 'filament',
        ]);

        AuditLogger::workspaceVaultItemCreated($this->record, ['source' => 'filament']);
    }
}
