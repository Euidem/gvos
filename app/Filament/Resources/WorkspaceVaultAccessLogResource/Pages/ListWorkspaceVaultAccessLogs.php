<?php

namespace App\Filament\Resources\WorkspaceVaultAccessLogResource\Pages;

use App\Filament\Resources\WorkspaceVaultAccessLogResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceVaultAccessLogs extends ListRecords
{
    protected static string $resource = WorkspaceVaultAccessLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
