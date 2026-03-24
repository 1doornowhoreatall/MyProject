<?php

namespace App\Filament\Pages;

use App\Models\Gateway;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;


class GatewayPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.gateway-page';

    public ?array $data = [];
    public Gateway $setting;

    /**
     * @dev
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla o acesso total à página
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla a visualização de elementos específicos
    }

    /**
     * @return void
     */
    public function mount(): void
    {
        $gateway = Gateway::first();
        if (!empty($gateway)) {
            $this->setting = $gateway;
            $this->form->fill($this->setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ADMINISTRATION PLATFORM')
                    ->description(new HtmlString('
                    <div style="font-weight: 600; display: flex; align-items: center;">
                        Configure your global settings.
                    </div>
            ')),


                Section::make('API GATEWAY KEYS')
                    ->description('Configure your API keys for CryptoCloud')
                    ->schema([
                        Section::make('CRYPTOCLOUD | 100% CRYPTO OFFSHORE')
                            ->description(new HtmlString('
                                <div style="display: flex; align-items: center;">
                                    Create your account to process crypto payments via CryptoCloud:
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
                                        href="https://cryptocloud.plus/"
                                        target="_blank">
                                        Open Account
                                    </a>
                                </div>
                        '),)
                            ->schema([
                                TextInput::make('cryptocloud_shop_id')
                                    ->label(__('SHOP ID'))
                                    ->placeholder(__('Enter CryptoCloud Shop ID'))
                                    ->maxLength(191)
                                    ->columnSpanFull(),
                                TextInput::make('cryptocloud_api_key')
                                    ->label(__('API KEY'))
                                    ->placeholder(__('Enter CryptoCloud API Key'))
                                    ->maxLength(191)
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Confirm Changes')
                            ->schema([
                                TextInput::make('admin_password')
                                    ->label(__('2FA Password (found in .env)'))
                                    ->placeholder(__('Enter 2FA password'))
                                    ->password()
                                    ->required()
                                    ->dehydrateStateUsing(fn($state) => null), // Do not persist
                            ]),

                    ]),
            ])
            ->statePath('data');
    }


    /**
     * @return void
     */
    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Atenção'))
                    ->body('Você não pode realizar esta alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }

            // Validação da senha de 2FA
            if (
                !isset($this->data['admin_password']) ||
                $this->data['admin_password'] !== env('TOKEN_DE_2FA')
            ) {
                Notification::make()
                    ->title(__('Acesso Negado'))
                    ->body('A senha de 2FA está incorreta. Você não pode atualizar os dados.')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Gateway::first();
            if (!empty($setting)) {
                if ($setting->update($this->data)) {
                    Notification::make()
                        ->title(__('ACESSE ONDAGAMES.COM'))
                        ->body('Suas configurações foram atualizadas com sucesso!')
                        ->success()
                        ->send();
                }
            } else {
                if (Gateway::create($this->data)) {
                    Notification::make()
                        ->title(__('ACESSE ONDAGAMES.COM'))
                        ->body('Suas configurações foram criadas com sucesso!')
                        ->success()
                        ->send();
                }
            }
        } catch (\Filament\Support\Exceptions\Halt $exception) {
            Notification::make()
                ->title(__('Erro ao alterar dados!'))
                ->body('Erro ao alterar dados!')
                ->danger()
                ->send();
        }
    }

}
