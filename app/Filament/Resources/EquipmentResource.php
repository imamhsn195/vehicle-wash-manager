<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('site_id')
                ->relationship('site', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('serial_number'),
            Forms\Components\DatePicker::make('purchase_date'),
            Forms\Components\TextInput::make('purchase_cost')->numeric()->prefix(fn () => currency_symbol()),
            Forms\Components\DatePicker::make('warranty_end'),
            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'under_maintenance' => 'Under Maintenance',
                    'retired' => 'Retired',
                ])
                ->default('active')
                ->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('site.name'),
                Tables\Columns\TextColumn::make('purchase_cost')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('purchase_date')->date(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'under_maintenance',
                        'danger' => 'retired',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('site')->relationship('site', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
