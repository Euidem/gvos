<?php

namespace App\Filament\Resources\WorkspaceTaskResource\Pages;

use App\Filament\Resources\WorkspaceTaskResource;
use App\Models\WorkspaceTask;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspaceTask extends CreateRecord
{
    protected static string $resource = WorkspaceTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set creator to the current admin user
        $data['created_by_user_id'] = auth()->id();

        // Auto-generate task code if not provided
        if (empty($data['task_code'])) {
            $data['task_code'] = WorkspaceTask::generateCode();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        AuditLogger::workspaceTaskCreated($this->record, [
            'source' => 'filament',
        ]);
    }
}
