<?php

namespace App\Filament\Resources\ManagerProfileResource\Pages;

use App\Filament\Resources\ManagerProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManagerProfiles extends ListRecords
{
    protected static string $resource = ManagerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
