<?php

namespace App\Filament\Resources\TrialResource\Pages;

use App\Filament\Resources\TrialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrials extends ListRecords
{
    protected static string $resource = TrialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
