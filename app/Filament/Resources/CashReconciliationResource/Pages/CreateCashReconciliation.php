<?php

namespace App\Filament\Resources\CashReconciliationResource\Pages;

use App\Filament\Resources\CashReconciliationResource;
use App\Services\CashReconciliationService;
use Filament\Resources\Pages\CreateRecord;

class CreateCashReconciliation extends CreateRecord
{
    protected static string $resource = CashReconciliationResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CashReconciliationService::class)->record($data, auth()->user());
    }
}
