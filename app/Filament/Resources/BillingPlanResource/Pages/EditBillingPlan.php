<?php

namespace App\Filament\Resources\BillingPlanResource\Pages;

use App\Filament\Resources\BillingPlanResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditBillingPlan extends EditRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function afterSave(): void
    {
        AuditLogger::billingPlanUpdated($this->record, ['updated_by' => auth()->id()]);
    }
}
