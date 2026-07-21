<?php

namespace App\Filament\Resources\DailyLogResource\Pages;

use App\Filament\Resources\DailyLogResource;
use App\Models\DailyLog;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

class CreateDailyLog extends CreateRecord
{
    protected static string $resource = DailyLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_by_id'] = auth()->id();
        $data['date'] = Carbon::parse($data['date'])->toDateString();

        return $data;
    }

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $existing = $this->findExistingDailyLog($data);

        if (! $existing) {
            return;
        }

        $this->redirectToExistingLog($existing);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $exception) {
            $existing = $this->findExistingDailyLog($data);

            if (! $existing) {
                throw $exception;
            }

            $this->redirectToExistingLog($existing);
        }
    }

    protected function findExistingDailyLog(array $data): ?DailyLog
    {
        return DailyLog::query()
            ->where('site_id', $data['site_id'])
            ->whereDate('date', Carbon::parse($data['date'])->toDateString())
            ->where('shift', $data['shift'] instanceof \BackedEnum ? $data['shift']->value : $data['shift'])
            ->first();
    }

    protected function redirectToExistingLog(DailyLog $existing): never
    {
        Notification::make()
            ->title(__('Daily log already exists'))
            ->body(__('Opening the existing log for this site, date, and shift.'))
            ->warning()
            ->send();

        $this->redirect(DailyLogResource::getUrl('edit', ['record' => $existing]));

        $this->halt();
    }
}
