<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashReconciliationResource\Pages;
use App\Models\CashReconciliation;
use App\Services\CashReconciliationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashReconciliationResource extends Resource
{
    protected static ?string $model = CashReconciliation::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Cash Reconciliation';

    protected static ?int $navigationSort = 2;


    public static function canAccess(): bool
    {
        return \App\Support\FilamentAccess::canAccessCashReconciliation();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (\App\Support\FilamentAccess::isAdmin() || \App\Support\FilamentAccess::isAccountant()) {
            return $query;
        }

        return $query->whereIn('site_id', \App\Support\FilamentAccess::managedSiteIds() ?: [0]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('site_id')
                ->relationship('site', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live(),
            Forms\Components\DatePicker::make('date')
                ->required()
                ->default(now())
                ->live(),
            Forms\Components\Placeholder::make('expected_preview')
                ->label('Expected Revenue (from wash logs)')
                ->content(function (Forms\Get $get) {
                    $siteId = $get('site_id');
                    $date = $get('date');
                    if (! $siteId || ! $date) {
                        return money_format_app(0);
                    }

                    $expected = app(CashReconciliationService::class)->expectedRevenue(
                        (int) $siteId,
                        \Illuminate\Support\Carbon::parse($date)->toDateString()
                    );

                    return money_format_app($expected);
                }),
            Forms\Components\TextInput::make('cash_collected')
                ->numeric()
                ->required()
                ->prefix(fn () => currency_symbol()),
            Forms\Components\TextInput::make('deposited_amount')
                ->numeric()
                ->required()
                ->prefix(fn () => currency_symbol()),
            Forms\Components\Toggle::make('is_deposited')->default(true),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('site.name'),
                Tables\Columns\TextColumn::make('expected_revenue')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('cash_collected')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('deposited_amount')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('difference')
                    ->label('Difference')
                    ->getStateUsing(fn (CashReconciliation $record) => $record->difference())
                    ->money(fn () => currency_code())
                    ->color(fn (CashReconciliation $record) => $record->hasDiscrepancy() ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_deposited')->boolean(),
            ])
            ->defaultSort('date', 'desc')
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
            'index' => Pages\ListCashReconciliations::route('/'),
            'create' => Pages\CreateCashReconciliation::route('/create'),
            'edit' => Pages\EditCashReconciliation::route('/{record}/edit'),
        ];
    }
}
