<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Filament\Resources\PartnerResource\RelationManagers\SettlementsRelationManager;
use App\Filament\Resources\PartnerResource\RelationManagers\SiteSharesRelationManager;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Partners & Payroll';

    protected static ?int $navigationSort = 1;


    public static function canAccess(): bool
    {
        return \App\Support\FilamentAccess::canAccessPartners();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (\App\Support\FilamentAccess::isAdmin() || \App\Support\FilamentAccess::isAccountant()) {
            return $query;
        }

        $partnerId = auth()->user()?->partnerProfile?->id;

        return $query->where('id', $partnerId ?? 0);
    }

    public static function canCreate(): bool
    {
        return \App\Support\FilamentAccess::isAdmin() || \App\Support\FilamentAccess::isAccountant();
    }

    public static function canEdit($record): bool
    {
        return \App\Support\FilamentAccess::isAdmin() || \App\Support\FilamentAccess::isAccountant();
    }

    public static function canDelete($record): bool
    {
        return \App\Support\FilamentAccess::isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\TextInput::make('phone'),
            Forms\Components\TextInput::make('global_share_pct')
                ->numeric()
                ->suffix('%')
                ->label('Default Share %'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('siteShares_count')
                    ->counts('siteShares')
                    ->label('Sites'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SiteSharesRelationManager::class,
            SettlementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
