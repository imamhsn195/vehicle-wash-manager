<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\StaffAssignment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $siteId = $this->form->getState()['site_id'] ?? null;

        if ($siteId) {
            StaffAssignment::updateOrCreate(
                ['staff_id' => $this->record->id, 'is_primary' => true],
                ['site_id' => $siteId, 'start_date' => now()->toDateString()]
            );
        }
    }
}
