<?php

namespace App\Filament\Resources\PartnerResource\RelationManagers;

use App\Services\PartnerPayoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SettlementsRelationManager extends RelationManager
{
    protected static string $relationship = 'settlements';

    protected static ?string $title = 'Settlements';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->disabled(),
            Forms\Components\Textarea::make('notes'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_start')->date(),
                Tables\Columns\TextColumn::make('period_end')->date(),
                Tables\Columns\TextColumn::make('amount')->money(fn () => currency_code()),
                Tables\Columns\IconColumn::make('paid_at')
                    ->label('Paid')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->paid_at !== null),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate')
                    ->label('Generate This Month')
                    ->icon('heroicon-o-calculator')
                    ->action(function () {
                        $partner = $this->getOwnerRecord();
                        $settlement = app(PartnerPayoutService::class)->createSettlement(
                            $partner,
                            now()->year,
                            now()->month
                        );

                        Notification::make()
                            ->title(__('Settlement created').': '.money_format_app($settlement->amount))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->paid_at === null)
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(PartnerPayoutService::class)->markPaid($record)),
            ]);
    }
}
