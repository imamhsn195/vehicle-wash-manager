<?php

namespace App\Filament\Resources\DailyLogResource\RelationManagers;

use App\Enums\PaymentMethod;
use App\Models\ServiceType;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WashEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    protected static ?string $title = 'Wash Entries';

    public function form(Form $form): Form
    {
        $siteId = $this->getOwnerRecord()->site_id;

        return $form->schema([
            Forms\Components\Select::make('staff_id')
                ->label('Staff')
                ->options(
                    Staff::whereHas('assignments', fn ($q) => $q->where('site_id', $siteId))
                        ->pluck('name', 'id')
                )
                ->required()
                ->searchable(),
            Forms\Components\Select::make('service_type_id')
                ->label('Service')
                ->options(ServiceType::where('site_id', $siteId)->pluck('name', 'id'))
                ->required()
                ->default(ServiceType::where('site_id', $siteId)->where('is_active', true)->value('id')),
            Forms\Components\TextInput::make('vehicle_count')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),
            Forms\Components\Select::make('payment_method')
                ->options(collect(PaymentMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()]))
                ->required()
                ->default(PaymentMethod::Cash->value),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name'),
                Tables\Columns\TextColumn::make('vehicle_count')->label('Cars'),
                Tables\Columns\TextColumn::make('serviceType.price')
                    ->label('Unit Price')
                    ->money('BDT'),
                Tables\Columns\TextColumn::make('payment_method')->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
