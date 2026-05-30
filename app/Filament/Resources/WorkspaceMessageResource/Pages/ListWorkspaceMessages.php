<?php

namespace App\Filament\Resources\WorkspaceMessageResource\Pages;

use App\Filament\Resources\WorkspaceMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceMessages extends ListRecords
{
    protected static string $resource = WorkspaceMessageResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Messages are posted via the portal only
    }
}
