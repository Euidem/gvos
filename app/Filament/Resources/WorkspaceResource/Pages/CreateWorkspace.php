<?php

namespace App\Filament\Resources\WorkspaceResource\Pages;

use App\Filament\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspace extends CreateRecord
{
    protected static string $resource = WorkspaceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate workspace code if not provided
        if (empty($data['workspace_code'])) {
            $data['workspace_code'] = Workspace::generateCode();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        AuditLogger::workspaceCreated($this->record);
    }
}
