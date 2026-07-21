<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollRecordResource\Pages;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollRecordResource extends Resource
{
    protected static ?string $model = PayrollRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Partners & Payroll';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('staff_id')
                ->relationship('staff', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\DatePicker::make('period_start')->required(),
            Forms\Components\DatePicker::make('period_end')->required(),
            Forms\Components\TextInput::make('base_amount')->numeric()->prefix(fn () => currency_symbol()),
            Forms\Components\TextInput::make('wash_bonus')->numeric()->prefix(fn () => currency_symbol()),
            Forms\Components\TextInput::make('deductions')->numeric()->prefix(fn () => currency_symbol()),
            Forms\Components\TextInput::make('net_amount')->numeric()->prefix(fn () => currency_symbol())->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')->searchable(),
                Tables\Columns\TextColumn::make('salary_type')->badge(),
                Tables\Columns\TextColumn::make('period_start')->date(),
                Tables\Columns\TextColumn::make('period_end')->date(),
                Tables\Columns\TextColumn::make('days_worked'),
                Tables\Columns\TextColumn::make('cars_washed'),
                Tables\Columns\TextColumn::make('base_amount')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('wash_bonus')->money(fn () => currency_code()),
                Tables\Columns\TextColumn::make('net_amount')->money(fn () => currency_code())->weight('bold'),
                Tables\Columns\IconColumn::make('paid_at')
                    ->label('Paid')
                    ->boolean()
                    ->getStateUsing(fn (PayrollRecord $record) => $record->paid_at !== null),
            ])
            ->defaultSort('period_end', 'desc')
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PayrollRecord $record) => $record->paid_at === null)
                    ->requiresConfirmation()
                    ->action(fn (PayrollRecord $record) => app(PayrollService::class)->markPaid($record)),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollRecords::route('/'),
            'edit' => Pages\EditPayrollRecord::route('/{record}/edit'),
        ];
    }
}
