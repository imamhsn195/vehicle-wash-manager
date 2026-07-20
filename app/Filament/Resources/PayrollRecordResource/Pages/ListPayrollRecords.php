<?php

namespace App\Filament\Resources\PayrollRecordResource\Pages;

use App\Filament\Resources\PayrollRecordResource;
use App\Models\Site;
use App\Services\PayrollService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrollRecords extends ListRecords
{
    protected static string $resource = PayrollRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label('Generate for Site')
                ->icon('heroicon-o-calculator')
                ->form([
                    Forms\Components\Select::make('site_id')
                        ->label('Site')
                        ->options(Site::pluck('name', 'id'))
                        ->required(),
                    Forms\Components\DatePicker::make('period_start')
                        ->default(now()->startOfMonth())
                        ->required(),
                    Forms\Components\DatePicker::make('period_end')
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $site = Site::findOrFail($data['site_id']);
                    $records = app(PayrollService::class)->generateForSite(
                        $site,
                        \Illuminate\Support\Carbon::parse($data['period_start']),
                        \Illuminate\Support\Carbon::parse($data['period_end'])
                    );

                    Notification::make()
                        ->title($records->count().' payroll records generated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
