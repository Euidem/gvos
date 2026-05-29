<?php

namespace App\Filament\Resources\PriceEstimateResource\Pages;

use App\Filament\Resources\PriceEstimateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriceEstimates extends ListRecords
{
    protected static string $resource = PriceEstimateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
