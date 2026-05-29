<?php

namespace App\Filament\Resources\PriceEstimateResource\Pages;

use App\Filament\Resources\PriceEstimateResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceEstimate extends CreateRecord
{
    protected static string $resource = PriceEstimateResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('price_estimate.created', $this->record, [
            'lead_request_id' => $this->record->lead_request_id,
            'amount'          => $this->record->estimated_amount,
            'currency'        => $this->record->currency,
            'status'          => $this->record->status,
        ]);

        // Update lead status to price_estimated when first estimate is created (if still 'new' or 'under_review')
        $lead = $this->record->leadRequest;
        if ($lead && in_array($lead->status, ['new', 'under_review'])) {
            $old = $lead->status;
            $lead->update(['status' => 'price_estimated']);
            AuditLogger::log('lead_request.status_changed', $lead, [
                'from' => $old, 'to' => 'price_estimated',
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
