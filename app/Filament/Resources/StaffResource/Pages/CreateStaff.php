<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\StaffAssignment;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = auth()->user()->organization_id ?? 1;

        return $data;
    }

    protected function afterCreate(): void
    {
        $siteId = $this->form->getState()['site_id'] ?? null;

        if ($siteId) {
            StaffAssignment::create([
                'staff_id' => $this->record->id,
                'site_id' => $siteId,
                'is_primary' => true,
                'start_date' => now()->toDateString(),
            ]);
        }
    }
}
