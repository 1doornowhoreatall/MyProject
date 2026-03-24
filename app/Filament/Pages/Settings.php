<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Filament\Pages;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Actions\Action;


class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $modelLabel = 'Configurações';

    protected static ?string $title = 'Configurações';

    protected static ?string $slug = 'configuracoes';

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

    public ?array $data = [];
    public Setting $setting;

    /**
     * @dev  
     * @return void
     */
    public function mount(): void
    {
        $this->setting = Setting::first();
        $this->form->fill($this->setting->toArray());
    }

    /**
     * @dev  
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes do Site')
                    ->schema([
                        TextInput::make('software_name')
                            ->label(__('Nome'))
                            ->required()
                            ->maxLength(191)
                            
                    ])->columns(2),

                Section::make('Logos')
                    ->schema([
                        FileUpload::make('software_favicon')
                            ->label(__('Favicon'))
                            ->placeholder(__('Carregue um favicon'))
                            ->image(),
                        FileUpload::make('software_logo_white')
                            ->label(__('Logo Branca'))
                            ->placeholder(__('Carregue uma logo branca'))
                            ->image(),
                        FileUpload::make('software_logo_black')
                            ->label(__('Logo Escura'))
                            ->placeholder(__('Carregue uma logo escura'))
                            ->image(),
                    ])->columns(3),

                Section::make('Depositos e Saques')
                    ->schema([
                        TextInput::make('min_deposit')
                            ->label(__('Min Deposito'))
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_deposit')
                            ->label(__('Max Deposito'))
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('min_withdrawal')
                            ->label(__('Min Saque'))
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_withdrawal')
                            ->label(__('Max Saque'))
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('rollover')
                            ->label(__('Rollover'))
                            ->numeric()
                            ->maxLength(191),
                    ])->columns(5),

                Section::make('Futebol')
                    ->description('Configurações de Futebol')
                    ->schema([
                        TextInput::make('soccer_percentage')
                            ->label(__('Futebol Comissão (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),

                        Toggle::make('turn_on_football')
                            ->inline(false)
                            ->label(__('Ativar Futebol')),
                    ])->columns(2),

                Section::make('Taxas')
                    ->description('Configurações de Ganhos da Plataforma')
                    ->schema([
                        TextInput::make('revshare_percentage')
                            ->label(__('RevShare (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        Toggle::make('revshare_reverse')
                            ->inline(false)
                            ->label(__('Ativar RevShare Negativo'))
                            ->helperText('Esta opção possibilita que o afiliado acumule saldos negativos decorrentes das perdas geradas pelos seus indicados.')
                        ,
                        TextInput::make('ngr_percent')
                            ->label(__('NGR (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                    ])->columns(3),
                Section::make('Dados Gerais')
                    ->schema([
                        TextInput::make('initial_bonus')
                            ->label(__('Bônus Inicial (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        TextInput::make('currency_code')
                            ->label(__('Moeda'))
                            ->maxLength(191),
                        Select::make('decimal_format')->options([
                            'dot' => 'Dot',
                        ]),
                        Select::make('currency_position')->options([
                            'left' => 'Left',
                            'right' => 'Right',
                        ]),
                        Toggle::make('disable_spin')
                            ->label(__('Disable Spin'))
                        ,
                        Toggle::make('suitpay_is_enable')
                            ->label(__('SuitPay Ativo'))
                        ,
                        Toggle::make('ezzepay_is_enable')
                        ->label(__('EzzePay Ativo'))
                        ,
                        Toggle::make('digito_is_enable')
                        ->label(__('DigitoPay Ativo'))
                        ,
                        Toggle::make('bspay_is_enable')
                            ->label(__('BsPay Ativo'))
                        ,
                    ])->columns(4),
            ])
            ->statePath('data');
    }
    
    /**
     * @dev  
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    /**
     *
     * @dev  
     * @return array
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Submit'))
                ->action(fn () => $this->submit())
                ->submit('submit')
            //->url(route('filament.admin.pages.dashboard'))
            ,
        ];
    }

    /**
     * @dev  
     * @param $array
     * @return mixed|void
     */
    private function uploadFile($array)
    {
        if(!empty($array) && is_array($array) || !empty($array) && is_object($array)) {
            foreach ($array as $k => $temporaryFile) {
                if ($temporaryFile instanceof TemporaryUploadedFile) {
                    $path = \Helper::upload($temporaryFile);
                    if($path) {
                        return $path['path'];
                    }
                }else{
                    return $temporaryFile;
                }
            }
        }
    }


    /**
     * @dev  
     * @return void
     */
    public function submit(): void
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Atenção'))
                    ->body('Você não pode realizar está alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }


            $setting = Setting::first();

            if(!empty($setting)) {

                $favicon   = $this->data['software_favicon'];
                $logoWhite = $this->data['software_logo_white'];
                $logoBlack = $this->data['software_logo_black'];

                if (is_array($favicon) || is_object($favicon)) {
                    if(!empty($favicon)) {
                        $this->data['software_favicon'] = $this->uploadFile($favicon);
                    }
                }

                if (is_array($logoWhite) || is_object($logoWhite)) {
                    if(!empty($logoWhite)) {
                        $this->data['software_logo_white'] = $this->uploadFile($logoWhite);
                    }
                }

                if (is_array($logoBlack) || is_object($logoBlack)) {
                    if(!empty($logoBlack)) {
                        $this->data['software_logo_black'] = $this->uploadFile($logoBlack);
                    }
                }

                if($setting->update($this->data)) {

                    Cache::put('setting', $setting);

                    Notification::make()
                        ->title(__('ACESSE ONDAGAMES.COM'))
                        ->body('Dados alterados com sucesso!')
                        ->success()
                        ->send();

                    redirect(route('filament.admin.pages.dashboard-admin'));

                }
            }


        } catch (Halt $exception) {
            Notification::make()
                ->title(__('Erro ao alterar dados!'))
                ->body('Erro ao alterar dados!')
                ->danger()
                ->send();
        }
    }


}
