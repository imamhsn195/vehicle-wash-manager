<?php

namespace App\Filament\Resources\DailyLogResource\Pages;

use App\Filament\Resources\DailyLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyLog extends CreateRecord
{
    protected static string $resource = DailyLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_by_id'] = auth()->id();

        return $data;
    }
}
