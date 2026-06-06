<?php

namespace App\Filament\Resources\WorkspaceSubscriptionResource\Pages;

use App\Filament\Resources\WorkspaceSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceSubscriptions extends ListRecords
{
    protected static string $resource = WorkspaceSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
