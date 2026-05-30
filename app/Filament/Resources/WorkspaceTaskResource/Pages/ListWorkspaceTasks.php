<?php

namespace App\Filament\Resources\WorkspaceTaskResource\Pages;

use App\Filament\Resources\WorkspaceTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceTasks extends ListRecords
{
    protected static string $resource = WorkspaceTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
