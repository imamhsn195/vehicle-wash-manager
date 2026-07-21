<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\SiteResource\RelationManagers\ServiceTypesRelationManager;
use App\Models\Site;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Site Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mall_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('manager_id')
                            ->label('Site Manager')
                            ->options(fn () => User::query()->where('role', 'site_manager')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->label('Daily Capacity (cars)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('serviceTypes')->withCount('serviceTypes'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager'),
                Tables\Columns\TextColumn::make('services_summary')
                    ->label('Services & Prices')
                    ->getStateUsing(fn (Site $record) => $record->serviceTypes
                        ->map(fn ($service) => sprintf(
                            '%s — ৳%s%s',
                            $service->name,
                            number_format((float) $service->price, 0),
                            $service->is_active ? '' : ' (inactive)'
                        ))
                        ->values()
                        ->all())
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->placeholder('No services'),
                Tables\Columns\TextColumn::make('serviceTypes_count')
                    ->label('Services')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ServiceTypesRelationManager::class,
            ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
