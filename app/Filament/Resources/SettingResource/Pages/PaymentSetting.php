<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Game;
use App\Models\Setting;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentSetting extends Page implements HasForms
{
    use HasPageSidebar, InteractsWithForms;

    protected static string $resource = SettingResource::class;
    protected static string $view = 'filament.resources.setting-resource.pages.payment-setting';

    public Setting $record;
    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __('FINANCIAL AREA');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function mount(): void
    {
        $setting = Setting::first();
        $this->record = $setting;
        $this->form->fill($setting->toArray());
    }

    public function save()
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title('Attention')
                    ->body('You cannot perform this action in demo mode')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Setting::find($this->record->id);

            if ($setting->update($this->data)) {
                Cache::put('setting', $setting);

                Notification::make()
                    ->title('Data changed')
                    ->body('Data changed successfully!')
                    ->success()
                    ->send();

                return redirect(route('filament.admin.resources.settings.payment', ['record' => $this->record->id]));
            }
        } catch (Halt $exception) {
            return;
        }
    }

    public function form(Form $form): Form
    {
        $games = Game::pluck('game_code', 'game_code');

        return $form
            ->schema([
                Section::make('PLATFORM CREATED FOR YOU')
                    ->description(new HtmlString('
                    <div style="font-weight: 600; display: flex; align-items: center;">
                        SAIBA MAIS SOBRE NÓS. PARTICIPE DA NOSSA COMUNIDADE IGAMING. ACESSE AGORA!
                        <a class="dark:text-white"
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #00b91e;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           "
                           href="https://obetzera.com"
                           target="_blank">
                            SITE OFICIAL
                        </a>
                        <a class="dark:text-white"
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #00b91e;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           "
                           href="https://t.me/obetzera01"
                           target="_blank">
                            GRUPO TELEGRAM
                        </a>
                    </div>
                ')),

                Section::make('CPA COMMISSION ADJUSTMENT')
                    ->description('Adjust the CPA commission value and minimum deposit for the affiliate to earn CPA.')
                    ->schema([
                        TextInput::make('cpa_baseline')
                            ->label('CPA MINIMUM DEPOSIT')
                            ->helperText('Minimum amount the user must deposit for the affiliate to earn CPA.')
                            ->numeric()
                            ->suffix('€ ')
                            ->maxLength(191),
                        TextInput::make('cpa_value')
                            ->label('CPA AFFILIATE')
                            ->helperText('CPA commission value the affiliate will earn.')
                            ->numeric()
                            ->suffix('€ ')
                            ->maxLength(191)
                    ])->columns(2),

                Section::make('ADJUST PAYMENT SETTINGS')
                    ->description('You can adjust withdrawal, deposit, and limits platform')
                    ->schema([
                        Select::make("saque")
                            ->label("WITHDRAWAL SYSTEM RESPONSIBLE")
                            ->options([
                                "ezzepay" => "EzzePay",
                                "suitpay" => "SuitPay",
                                "digitopay" => "Digito Pay",
                                "ondapay" => "OndaPay",
                                "bspay" => "BsPay"

                            ]),
                        TextInput::make('min_deposit')
                            ->label('MINIMUM DEPOSIT')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_deposit')
                            ->label('MAXIMUM DEPOSIT')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('min_withdrawal')
                            ->label('MINIMUM WITHDRAWAL')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_withdrawal')
                            ->label('MAXIMUM WITHDRAWAL')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('initial_bonus')
                            ->label('BONUS PERCENTAGE')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        Section::make('PAYMENT GATEWAYS')
                            ->description('Enable or disable your preferred gateways.')
                            ->schema([
                                Toggle::make('cryptocloud_is_enable')
                                    ->label(__('CryptoCloud Active')),
                                Toggle::make('stripe_is_enable')
                                    ->label(__('Stripe Active')),
                            ])->columns(3),
                    ])->columns(2)
            ])
            ->statePath('data');
    }
}
