<?php

namespace App\Filament\Resources\BillingPlanResource\Pages;

use App\Filament\Resources\BillingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBillingPlans extends ListRecords
{
    protected static string $resource = BillingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
