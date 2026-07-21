<?php

namespace App\Filament\Resources;

use App\Enums\Shift;
use App\Filament\Resources\DailyLogResource\Pages;
use App\Filament\Resources\DailyLogResource\RelationManagers\WashEntriesRelationManager;
use App\Models\DailyLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DailyLogResource extends Resource
{
    protected static ?string $model = DailyLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;


    public static function canAccess(): bool
    {
        return \App\Support\FilamentAccess::canAccessDailyLogs();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (\App\Support\FilamentAccess::isAdmin() || \App\Support\FilamentAccess::isAccountant()) {
            return $query;
        }

        return $query->whereIn('site_id', \App\Support\FilamentAccess::managedSiteIds() ?: [0]);
    }

    protected static ?string $navigationLabel = 'Daily Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('site_id')
                    ->relationship('site', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->rules([
                        fn (Forms\Get $get, ?DailyLog $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (! $value || ! $get('date') || ! $get('shift')) {
                                return;
                            }

                            $exists = DailyLog::query()
                                ->where('site_id', $value)
                                ->whereDate('date', \Illuminate\Support\Carbon::parse($get('date'))->toDateString())
                                ->where('shift', $get('shift'))
                                ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                ->exists();

                            if ($exists) {
                                $fail(__('A daily log already exists for this site, date, and shift.'));
                            }
                        },
                    ]),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->live(),
                Forms\Components\Select::make('shift')
                    ->options(collect(Shift::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->required()
                    ->live(),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
                Forms\Components\Toggle::make('is_closed')->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('site.name')->sortable(),
                Tables\Columns\TextColumn::make('shift')->badge(),
                Tables\Columns\TextColumn::make('total_cars')
                    ->label('Cars')
                    ->getStateUsing(fn (DailyLog $record) => $record->totalCars()),
                Tables\Columns\TextColumn::make('submittedBy.name')->label('Submitted By'),
                Tables\Columns\IconColumn::make('is_closed')->boolean(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('site')->relationship('site', 'name'),
                Tables\Filters\SelectFilter::make('shift')
                    ->options(collect(Shift::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WashEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyLogs::route('/'),
            'create' => Pages\CreateDailyLog::route('/create'),
            'edit' => Pages\EditDailyLog::route('/{record}/edit'),
        ];
    }
}
