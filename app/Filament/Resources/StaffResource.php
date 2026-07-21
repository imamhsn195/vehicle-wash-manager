<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\Site;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('employee_code'),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\Select::make('staff_type')
                            ->options([
                                'washer' => 'Washer',
                                'supervisor' => 'Supervisor',
                                'manager' => 'Manager',
                            ])
                            ->required(),
                        Forms\Components\Select::make('site_id')
                            ->label('Assigned Site')
                            ->options(Site::pluck('name', 'id'))
                            ->required()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Select $component, ?Staff $record) {
                                if ($record) {
                                    $component->state($record->assignments()->where('is_primary', true)->value('site_id'));
                                }
                            }),
                        Forms\Components\Toggle::make('is_active')->default(true),
                    ])->columns(2),
                Forms\Components\Section::make('Pay & Benefits')
                    ->schema([
                        Forms\Components\Select::make('salary_type')
                            ->options([
                                'daily' => 'Daily Wage',
                                'monthly' => 'Monthly Salary',
                                'per_car' => 'Per Car',
                                'hybrid' => 'Hybrid',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('base_salary')->numeric()->prefix(fn () => currency_symbol()),
                        Forms\Components\TextInput::make('per_wash_rate')->numeric()->prefix(fn () => currency_symbol()),
                        Forms\Components\Toggle::make('has_housing')->default(true),
                        Forms\Components\TextInput::make('daily_food_allowance')
                            ->numeric()
                            ->prefix(fn () => currency_symbol())
                            ->default(100),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')->label('Code'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('staff_type')->badge(),
                Tables\Columns\TextColumn::make('primary_site')
                    ->label('Site')
                    ->getStateUsing(fn (Staff $record) => $record->assignments()->where('is_primary', true)->first()?->site?->name),
                Tables\Columns\TextColumn::make('salary_type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
