<?php

namespace App\Filament\Resources\PartnerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SiteSharesRelationManager extends RelationManager
{
    protected static string $relationship = 'siteShares';

    protected static ?string $title = 'Site Shares';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('site_id')
                ->relationship('site', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('share_pct')
                ->numeric()
                ->required()
                ->suffix('%')
                ->minValue(0)
                ->maxValue(100),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site.name'),
                Tables\Columns\TextColumn::make('share_pct')->suffix('%'),
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
