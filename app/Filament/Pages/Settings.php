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

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?string $slug = 'settings';

    /**
     * @dev  
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin'); // Controls full access to the page
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin'); // Controls the viewing of specific elements
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
                Section::make('Site Details')
                    ->schema([
                        TextInput::make('software_name')
                            ->label('Name')
                            ->required()
                            ->maxLength(191)
                            
                    ])->columns(2),

                Section::make('Logos')
                    ->schema([
                        FileUpload::make('software_favicon')
                            ->label(__('Favicon'))
                            ->placeholder('Upload a favicon')
                            ->image(),
                        FileUpload::make('software_logo_white')
                            ->label('White Logo')
                            ->placeholder('Upload a white logo')
                            ->image(),
                        FileUpload::make('software_logo_black')
                            ->label('Dark Logo')
                            ->placeholder('Upload a dark logo')
                            ->image(),
                    ])->columns(3),

                Section::make('Deposits and Withdrawals')
                    ->schema([
                        TextInput::make('min_deposit')
                            ->label('Min Deposit')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_deposit')
                            ->label('Max Deposit')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('min_withdrawal')
                            ->label('Min Withdrawal')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_withdrawal')
                            ->label('Max Withdrawal')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('rollover')
                            ->label(__('Rollover'))
                            ->numeric()
                            ->maxLength(191),
                    ])->columns(5),

                Section::make('Football')
                    ->description('Football Settings')
                    ->schema([
                        TextInput::make('soccer_percentage')
                            ->label('Football Commission (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),

                        Toggle::make('turn_on_football')
                            ->inline(false)
                            ->label('Turn on Football'),
                    ])->columns(2),

                Section::make('Fees')
                    ->description('Platform Profit Settings')
                    ->schema([
                        TextInput::make('revshare_percentage')
                            ->label(__('RevShare (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        Toggle::make('revshare_reverse')
                            ->inline(false)
                            ->label('Enable Negative RevShare')
                            ->helperText('This option allows the affiliate to accumulate negative balances resulting from losses generated by their referrals.')
                        ,
                        TextInput::make('ngr_percent')
                            ->label(__('NGR (%)'))
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                    ])->columns(3),
                Section::make('General Data')
                    ->schema([
                        TextInput::make('initial_bonus')
                            ->label('Initial Bonus (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        TextInput::make('currency_code')
                            ->label('Currency')
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
                        Toggle::make('cryptocloud_is_enable')
                            ->label('CryptoCloud Active')
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
                    ->title('Attention')
                    ->body('You cannot make this change in the demo version')
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
                        ->title(config('app.name'))
                        ->body('Data updated successfully!')
                        ->success()
                        ->send();

                    redirect(route('filament.admin.pages.dashboard-admin'));

                }
            }


        } catch (Halt $exception) {
            Notification::make()
                ->title('Error updating data!')
                ->body('Error updating data!')
                ->danger()
                ->send();
        }
    }


}
