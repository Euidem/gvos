<?php

namespace App\Filament\Resources\WorkspaceSubscriptionResource\Pages;

use App\Filament\Resources\WorkspaceSubscriptionResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspaceSubscription extends CreateRecord
{
    protected static string $resource = WorkspaceSubscriptionResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::subscriptionCreated($this->record, ['created_by' => auth()->id()]);
    }
}
