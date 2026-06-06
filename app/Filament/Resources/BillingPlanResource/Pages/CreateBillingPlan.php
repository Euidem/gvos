<?php

namespace App\Filament\Resources\BillingPlanResource\Pages;

use App\Filament\Resources\BillingPlanResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingPlan extends CreateRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::billingPlanCreated($this->record, ['created_by' => auth()->id()]);
    }
}
