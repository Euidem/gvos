<?php

namespace App\Filament\Resources\LeadRequestResource\Pages;

use App\Filament\Resources\LeadRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeadRequests extends ListRecords
{
    protected static string $resource = LeadRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
