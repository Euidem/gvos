<?php

namespace App\Filament\Resources\WorkspaceVaultItemResource\Pages;

use App\Filament\Resources\WorkspaceVaultItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceVaultItems extends ListRecords
{
    protected static string $resource = WorkspaceVaultItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
