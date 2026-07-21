<?php

namespace App\Filament\Resources;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Services\ExpenseService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;


    public static function canAccess(): bool
    {
        return \App\Support\FilamentAccess::canAccessExpenses();
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
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('type')
                ->options(collect(ExpenseType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                ->required(),
            Forms\Components\Select::make('category')
                ->options(collect(ExpenseCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                ->required(),
            Forms\Components\TextInput::make('description')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('amount')->numeric()->required()->prefix(fn () => currency_symbol()),
            Forms\Components\DatePicker::make('date')->required()->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('site.name')->label('Site')->placeholder('Org-wide'),
                Tables\Columns\TextColumn::make('category')
                    ->formatStateUsing(fn ($state) => $state instanceof ExpenseCategory ? $state->label() : $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('description')->limit(30),
                Tables\Columns\TextColumn::make('amount')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => $state instanceof ExpenseType ? $state->label() : $state),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('submittedBy.name')->label('Submitted By'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(ExpenseStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('site')->relationship('site', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn (Expense $record) => $record->isPending()),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Expense $record) => $record->isPending() && \App\Support\FilamentAccess::canApproveExpenses())
                    ->action(fn (Expense $record) => app(ExpenseService::class)->approve($record, auth()->user())),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Expense $record) => $record->isPending() && \App\Support\FilamentAccess::canApproveExpenses())
                    ->action(fn (Expense $record) => app(ExpenseService::class)->reject($record, auth()->user())),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
