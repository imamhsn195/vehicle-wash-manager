<?php

namespace App\Filament\Pages;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Enums\UserRole;
use App\Exceptions\NoActiveServiceTypeException;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\Staff;
use App\Services\DailyLogService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DailyLogEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static string $view = 'filament.pages.daily-log-entry';

    protected static ?string $navigationLabel = 'Quick Daily Log';

    protected static ?string $title = 'Quick Daily Log';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 0;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $siteId = $user->isSiteManager()
            ? $user->managedSites()->value('id')
            : Site::query()->value('id');

        $defaultServiceId = $siteId
            ? ServiceType::query()->where('site_id', $siteId)->where('is_active', true)->orderBy('id')->value('id')
            : null;

        $this->form->fill([
            'site_id' => $siteId,
            'service_type_id' => $defaultServiceId,
            'date' => now()->toDateString(),
            'shift' => Shift::Morning->value,
            'staff_id' => null,
            'vehicle_count' => 1,
            'payment_method' => PaymentMethod::Cash->value,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('site_id')
                    ->label(__('Site'))
                    ->options(fn () => $this->getAvailableSites())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $set('staff_id', null);
                        $set(
                            'service_type_id',
                            $state
                                ? ServiceType::query()->where('site_id', $state)->where('is_active', true)->orderBy('id')->value('id')
                                : null
                        );
                    })
                    ->disabled(fn () => auth()->user()?->isSiteManager())
                    ->dehydrated(),
                Forms\Components\Select::make('service_type_id')
                    ->label(__('Service'))
                    ->options(fn (Forms\Get $get) => $this->getServicesForSite($get('site_id')))
                    ->required()
                    ->helperText(__('Price comes from the selected service.')),
                Forms\Components\DatePicker::make('date')
                    ->label(__('Date'))
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('shift')
                    ->label(__('Shift'))
                    ->options(collect(Shift::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->label(__('Staff'))
                    ->options(fn (Forms\Get $get) => $this->getStaffForSite($get('site_id')))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('vehicle_count')
                    ->label(__('Cars Washed'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()]))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        try {
            app(DailyLogService::class)->recordEntry($data, auth()->user());
        } catch (NoActiveServiceTypeException) {
            Notification::make()
                ->title(__('No wash price configured for this site.'))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('Wash entry saved'))
            ->body(__(':count cars logged for :staff', [
                'count' => $data['vehicle_count'],
                'staff' => Staff::find($data['staff_id'])?->name,
            ]))
            ->success()
            ->send();

        $this->form->fill([
            ...$data,
            'staff_id' => null,
            'vehicle_count' => 1,
        ]);
    }

    protected function getAvailableSites(): array
    {
        $user = auth()->user();

        if ($user?->role === UserRole::Admin || $user?->role === UserRole::Accountant) {
            return Site::query()->pluck('name', 'id')->all();
        }

        return $user?->managedSites()->pluck('name', 'id')->all() ?? [];
    }

    protected function getStaffForSite(?int $siteId): array
    {
        if (! $siteId) {
            return [];
        }

        return Staff::query()
            ->whereHas('assignments', fn ($q) => $q->where('site_id', $siteId))
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->all();
    }

    protected function getServicesForSite(?int $siteId): array
    {
        if (! $siteId) {
            return [];
        }

        return ServiceType::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (ServiceType $service) => [
                $service->id => sprintf('%s (%s)', $service->name, money_format_app($service->price)),
            ])
            ->all();
    }
}
