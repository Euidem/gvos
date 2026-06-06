<?php

namespace App\Filament\Resources\WorkspaceSubscriptionResource\Pages;

use App\Filament\Resources\WorkspaceSubscriptionResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditWorkspaceSubscription extends EditRecord
{
    protected static string $resource = WorkspaceSubscriptionResource::class;

    protected function afterSave(): void
    {
        AuditLogger::subscriptionUpdated($this->record, ['updated_by' => auth()->id()]);
    }
}
