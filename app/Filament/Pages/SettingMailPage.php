<?php

namespace App\Filament\Pages;

use App\Models\SettingMail;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Filament\Forms\Components\Select; // Adicione esta linha

class SettingMailPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.setting-mail-page';

    public ?array $data = [];
    public SettingMail $setting;

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
        $smtp = SettingMail::first();
        if(!empty($smtp)) {
            $this->setting = $smtp;
            $this->form->fill($this->setting->toArray());
        }else{
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
                Section::make(__('FITOREBET PLATFORM'))
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
                Section::make(__('EMAIL SERVER CREDENTIALS'))
                    ->description(__('Enter your credentials for sending notification emails'))
                    ->schema([
                        Select::make('software_smtp_type')
                            ->label(__('PROTOCOL'))
                            ->placeholder(__('Select mailer'))
                            ->options([
                                'imap' => 'IMAP',
                                'smtp' => 'SMTP',
                                'pop' => 'POP',
                            ])
                            ->required(),
                        TextInput::make('software_smtp_mail_host')
                            ->label(__('SERVER ADDRESS'))
                            ->placeholder(__('Enter your mail host'))
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_port')
                            ->label(__('Port'))
                            ->placeholder(__('SERVER PORT'))
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_username')
                            ->label(__('User'))
                            ->placeholder(__('USERNAME'))
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_password')
                            ->label(__('Password'))
                            ->placeholder(__('USER PASSWORD'))
                            ->maxLength(191),
                        Select::make('software_smtp_mail_encryption')
                            ->label(__('ENCRYPTION'))
                            ->placeholder(__('Select encryption'))
                            ->options([
                                'ssl' => 'SSL',
                                'tls' => 'TLS',
                            ])
                            ->required(),
                        TextInput::make('software_smtp_mail_from_address')
                            ->label(__('EMAIL HEADER'))
                            ->placeholder(__('Enter Header Email Address'))
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_from_name')
                            ->label(__('HEADER NAME'))
                            ->placeholder(__('Enter Header Name'))
                            ->maxLength(191),
                    ])->columns(4),
            ])
            ->statePath('data');
    }



    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Attention'))
                    ->body(__('You cannot perform this change in the demo version'))
                    ->danger()
                    ->send();
                return;
            }

            $setting = SettingMail::first();
            if(!empty($setting)) {
                if(!empty($this->data['software_smtp_type'])) {
                    $envs = DotenvEditor::load(base_path('.env'));

                    $envs->setKeys([
                        'MAIL_MAILER' => $this->data['software_smtp_type'],
                        'MAIL_HOST' => $this->data['software_smtp_mail_host'],
                        'MAIL_PORT' => $this->data['software_smtp_mail_port'],
                        'MAIL_USERNAME' => $this->data['software_smtp_mail_username'],
                        'MAIL_PASSWORD' => $this->data['software_smtp_mail_password'],
                        'MAIL_ENCRYPTION' => $this->data['software_smtp_mail_encryption'],
                        'MAIL_FROM_ADDRESS' => $this->data['software_smtp_mail_from_address'],
                        'MAIL_FROM_NAME' => $this->data['software_smtp_mail_from_name'],
                    ]);

                    $envs->save();
                }

                Notification::make()
                    ->title(__('ACCESS FITOREBET.COM'))
                    ->body(__('Keys changed successfully!'))
                    ->success()
                    ->send();
            } else {
                if(SettingMail::create($this->data)) {
                    Notification::make()
                        ->title(__('ACCESS FITOREBET.COM'))
                        ->body(__('Keys created successfully!'))
                        ->success()
                        ->send();
                }
            }


        } catch (Halt $exception) {
            Notification::make()
                ->title(__('Error changing data!'))
                ->body(__('Error changing data!'))
                ->danger()
                ->send();
        }
    }
}
