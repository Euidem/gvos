<?php

namespace App\Filament\Resources\WorkspaceFileResource\Pages;

use App\Filament\Resources\WorkspaceFileResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceFiles extends ListRecords
{
    protected static string $resource = WorkspaceFileResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Files are uploaded via the portal only
    }
}
