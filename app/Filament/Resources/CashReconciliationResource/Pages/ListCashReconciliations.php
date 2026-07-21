<?php

namespace App\Filament\Resources\CashReconciliationResource\Pages;

use App\Filament\Resources\CashReconciliationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashReconciliations extends ListRecords
{
    protected static string $resource = CashReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
