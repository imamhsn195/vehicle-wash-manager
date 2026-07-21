<?php

namespace App\Filament\Resources\CashReconciliationResource\Pages;

use App\Filament\Resources\CashReconciliationResource;
use App\Services\CashReconciliationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashReconciliation extends EditRecord
{
    protected static string $resource = CashReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CashReconciliationService::class)->record([
            ...$data,
            'site_id' => $record->site_id,
            'date' => $data['date'] ?? $record->date->toDateString(),
        ], auth()->user());
    }
}
