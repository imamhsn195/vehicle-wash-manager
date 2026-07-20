<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Enums\ExpenseStatus;
use App\Filament\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = auth()->user()->organization_id ?? 1;
        $data['submitted_by_id'] = auth()->id();
        $data['status'] = ExpenseStatus::Pending->value;

        return $data;
    }
}
