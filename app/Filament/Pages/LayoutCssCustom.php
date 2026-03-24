<?php

namespace App\Filament\Pages;

use App\Helpers\Core;
use App\Models\CustomLayout;

use Creagia\FilamentCodeField\CodeField;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\HtmlString;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Toggle;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\CacheNuker;
use Illuminate\Support\Str;



class LayoutCssCustom extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.layout-css-custom';
    protected static ?string $navigationLabel = 'Layout Customization';
    protected static ?string $modelLabel = 'Layout Customization';
    protected static ?string $title = 'Layout Customization';
    protected static ?string $slug = 'custom-layout';

    public ?array $data = [];
    public CustomLayout $custom;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla o acesso total à página
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla a visualização de elementos específicos
    }

    public function mount(): void 
    {
        $this->custom = CustomLayout::first();
        $data = $this->custom->toArray();
        $this->form->fill($data);
    }
    



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getSecTokenJivochat(),
                $this->css_do_pesquisar_homepage(),
                $this->css_do_baixar_app(),
                $this->css_do_maior_de_18(),
                $this->css_do_rodadas_gratis(),
                $this->css_dos_maiores_ganhos(),
                $this->css_dos_lives_ganhos(),
                $this->css_do_bonus_diario(),
                $this->css_do_termos_sport(),
                $this->central_suporte(),
                $this->css_do_geral(),
                $this->css_do_menu_cell(),
                $this->css_do_missoes(),
                $this->css_do_vips(),
                $this->css_do_promocoes(),


                $this->css_do_BetHistory(),
                $this->css_do_WalletWithdrawal(),
                $this->css_do_CryptoWallet(),
                $this->css_do_WalletDeposit(),
                $this->css_do_WalletBalance(),
                $this->css_do_WalletDashboard(),
                
                $this->css_do_affiliates(),
                $this->css_do_login_registro_esquci(),
                $this->css_do_listgames(),
                $this->css_do_homepage(),
                $this->css_do_navbar(),
                $this->css_do_footer(),
                $this->css_do_sidebar(),
                $this->css_do_popup_cookies(),
                $this->css_do_myconta(),



                
                $this->getSectionPlatformTexts(),
                $this->getSectiimagensmanegem(),
                $this->getSectilinkmagem(),
                $this->getSectionCustomCode(),
                
                
            ])
            ->statePath('data');
    }
 



    
    protected function getSecTokenJivochat(): Section
    {
        return Section::make(__('JIVOCHAT TOKEN'))
            ->label(__('Change Jivochat token'))
            ->schema([
                \Filament\Forms\Components\Placeholder::make('limpar_cache')
                    ->label('')
                    ->content(new HtmlString(
                        '<div style="font-weight: 600; display: flex; align-items: center;">
                            <!-- Botão Limpar Cache -->
                            <a href="https://www.jivochat.com.br/?partner_id=47634" class="dark:text-white" 
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
                             );">
                                CHAT WEBSITE
                            </a>
                
                    
                        </div>'
                    )),
                TextInput::make("token_jivochat")
                    ->label(__('Jivochat Token Ex: //code.jivosite.com/widget/lmxxxxxxxx'))
                    ->placeholder(__('Enter Jivochat token here Ex: //code.jivosite.com/widget/lmxxxxxxxx')),
            ])->columns(['default' => 1]);
    }
    

    protected function css_do_baixar_app(): Section
    {
        return Section::make(__('Download App Page'))
            ->description(__('You can change the colors and image of the Download App section'))
            ->label(__('Download App'))
            ->schema([
                ColorPicker::make('baixar_app_background')->label(__('Background color'))->required(),
                ColorPicker::make('baixar_app_sub_background')->label(__('Secondary background color'))->required(),
                FileUpload::make('baixar_app_imagem')->label(__('App Image'))->image()->placeholder(__('Upload an image')),
                ColorPicker::make('baixar_app_texto_color')->label(__('Text color'))->required(),
                ColorPicker::make('baixar_app_explicacao_background')->label(__('Explanation background color'))->required(),
                ColorPicker::make('baixar_app_botao_background')->label(__('Button background color'))->required(),
                ColorPicker::make('baixar_app_botao_texto_color')->label(__('Button text color'))->required(),
            ])->columns(3);
    }



    protected function css_do_pesquisar_homepage(): Section
    {
        return Section::make(__('Search Homepage'))
            ->description(__('You can change the colors of the Homepage search bar'))
            ->label(__('Search Homepage'))
            ->schema([
                ColorPicker::make('pesquisar_homepage_background')->label(__('Search background color'))->required(),
                ColorPicker::make('pesquisar_homepage_texto_color')->label(__('Search text color'))->required(),
                ColorPicker::make('pesquisar_homepage_icon_color')->label(__('Search icon color'))->required(),
                ColorPicker::make('pesquisar_homepage_button_background')->label(__('Search button background color'))->required(),
                ColorPicker::make('pesquisar_homepage_button_text_color')->label(__('Search button text color'))->required(),
            ])->columns(['default' => 2]);
    }


    protected function getSectilinkmagem(): Section
    {
        return Section::make(__('COMPLEMENTARY LINKS'))
            ->label(__('Change complementary links'))
            ->schema([
                TextInput::make("link_suporte")->label(__("Support Link")),
                TextInput::make("link_lincenca")->label(__("License Link")),
                TextInput::make("link_app")->label(__("App Link")),
                TextInput::make("link_telegram")->label(__("Telegram Link")),
                TextInput::make("link_facebook")->label(__("Facebook Link")),
                TextInput::make("link_whatsapp")->label(__("WhatsApp Link")),
                TextInput::make("link_instagram")->label(__("Instagram Link")),
            ])->columns(['default' => 3]);
    }

    protected function getSectiimagensmanegem(): Section
    {
        return Section::make(__('banners and images'))
            ->label(__('Images and Banners'))
            ->schema([
                FileUpload::make('image_hot4')->label(__("License banner image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('banner_deposito1')->label(__("Deposit banner"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('banner_deposito2')->label(__("QR Code banner"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('banner_registro')->label(__("Register banner"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('banner_login')->label(__("Login banner"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_inicio')->label(__("Menu Start cell image"))->placeholder(__('Upload an image'))->image(),                
                FileUpload::make('menucell_suporte')->label(__("Menu Support cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_carteira')->label(__("Menu Wallet cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_afiliado')->label(__("Menu Affiliate cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_saque')->label(__("Menu Withdrawal cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_sair')->label(__("Menu Exit cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('menucell_img_esportes')->label(__("Sports cell image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_imagen1')->label(__("Footer Image 1"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_imagen2')->label(__("Footer Image 2"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_imagen3')->label(__("Footer Image 3"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_telegram')->label(__("Footer Telegram Image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_facebook')->label(__("Footer Facebook Image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_whatsapp')->label(__("Footer WhatsApp Image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_instagram')->label(__("Footer Instagram Image"))->placeholder(__('Upload an image'))->image(),
                FileUpload::make('footer_mais18')->label(__("Footer +18 Image"))->placeholder(__('Upload an image'))->image(),
            ])->columns(['default' => 4]);
    }

    protected function getSectionCustomCode(): Section
    {
        return Section::make()
            ->schema([
                TextInput::make('idPixelFC')->label(__("Id do Pixel Facebook")),
                TextInput::make('idPixelGoogle')->label(__("Id do Pixel Google")),
                CodeField::make('custom_css')->setLanguage(CodeField::CSS)->withLineNumbers()->minHeight(100),
                CodeField::make('custom_js')->setLanguage(CodeField::JS)->withLineNumbers()->minHeight(100),
            ]);
    }

    private function uploadFile($file)
    {
        // Se o arquivo for um caminho já existente (string), encapsula em um array.
        if (is_string($file)) {
            return [$file];
        }
    
        // Verifica se é um array ou objeto antes de tentar iterar
        if (!empty($file) && (is_array($file) || is_object($file))) {
            foreach ($file as $temporaryFile) {
                if ($temporaryFile instanceof TemporaryUploadedFile) {
                    // Chama o método Core::upload() para processar o arquivo
                    $path = Core::upload($temporaryFile);
    
                    // Verifica se o caminho foi retornado corretamente
                    return [$path['path'] ?? $temporaryFile];
                }
                return [$temporaryFile];
            }
        }
    
        // Se não for um array, objeto ou string válida, retorna null.
        return null;
    }





    /////////////////////////////////////////////////////////////////////
    ///////////////////////// CENTRAL DE DISIGN /////////////////////////
    /////////////////////////////////////////////////////////////////////


    // PAGINA NAVBAR   | FICA EM CIMA DO SITE
    protected function css_do_navbar(): Section
    {
        return Section::make(__('Navbar Page'))
            ->description(__('You can change the colors of the Navbar'))
            ->label(__('Navbar'))
            ->schema([
                ColorPicker::make('navbar_background')->label(__('Navbar background color'))->required(),
                ColorPicker::make('navbar_text')->label(__('Navbar text color'))->required(),
                ColorPicker::make('navbar_icon_menu')->label(__('Menu icon color'))->required(),
                ColorPicker::make('navbar_icon_promocoes')->label(__('Promotions icon color'))->required(),
                ColorPicker::make('navbar_icon_promocoes_segunda_cor')->label(__('Promotions icon secondary color'))->required(),
                ColorPicker::make('navbar_icon_casino')->label(__('Casino icon color'))->required(),
                ColorPicker::make('navbar_icon_sport')->label(__('Sports icon color'))->required(),
                ColorPicker::make('navbar_button_text_login')->label(__('Login button text color'))->required(),
                ColorPicker::make('navbar_button_text_registro')->label(__('Registration button text color'))->required(),
                ColorPicker::make('navbar_button_background_login')->label(__('Login button background color'))->required(),
                ColorPicker::make('navbar_button_background_registro')->label(__('Registration button background color'))->required(),
                ColorPicker::make('navbar_button_border_color')->label(__('Button border color'))->required(),
                ColorPicker::make('navbar_button_text_superior')->label(__('Superior button text color'))->required(),
                ColorPicker::make('navbar_button_background_superior')->label(__('Superior button background color'))->required(),
                ColorPicker::make('navbar_text_superior')->label(__('Superior text color'))->required(),
                ColorPicker::make('navbar_button_deposito_background')->label(__('Deposit button background color'))->required(),
                ColorPicker::make('navbar_button_deposito_text_color')->label(__('Deposit button text color'))->required(),
                ColorPicker::make('navbar_button_deposito_border_color')->label(__('Deposit button border color'))->required(),
                ColorPicker::make('navbar_button_deposito_píx_color_text')->label(__('Deposit button PIX text color'))->required(),
                ColorPicker::make('navbar_button_deposito_píx_background')->label(__('Deposit button PIX background color'))->required(),
                ColorPicker::make('navbar_button_deposito_píx_icon')->label(__('Deposit button PIX icon color'))->required(),
                ColorPicker::make('navbar_button_carteira_background')->label(__('Wallet button background color'))->required(),
                ColorPicker::make('navbar_button_carteira_text_color')->label(__('Wallet button text color'))->required(),
                ColorPicker::make('navbar_button_carteira_border_color')->label(__('Wallet button border color'))->required(),
                ColorPicker::make('navbar_perfil_text_color')->label(__('Profile text color'))->required(),
                ColorPicker::make('navbar_perfil_background')->label(__('Profile background color'))->required(),
                ColorPicker::make('navbar_perfil_icon_color')->label(__('Profile icon color'))->required(),
                ColorPicker::make('navbar_perfil_icon_color_border')->label(__('Profile icon border color'))->required(),
                ColorPicker::make('navbar_perfil_modal_icon_color')->label(__('Profile modal icon color'))->required(),
                ColorPicker::make('navbar_perfil_modal_text_modal')->label(__('Profile modal text color'))->required(),
                ColorPicker::make('navbar_perfil_modal_background_modal')->label(__('Profile modal background color'))->required(),
                ColorPicker::make('navbar_perfil_modal_hover_modal')->label(__('Profile modal hover color'))->required(),


            ])->columns(['default' => 4]);
    }
 

    // PAGINA SIDERBAR | FICA NA LATERAL DO SITE
    protected function css_do_sidebar(): Section
    {

        return Section::make(__('Sidebar Page'))
            ->description(__('You can change the colors of the Sidebar'))
            ->label(__('Sidebar'))
            ->schema([
            ColorPicker::make('sidebar_background')->label(__('Sidebar background color'))->required(),
            ColorPicker::make('sidebar_button_missoes_background')->label(__('Missions button background color'))->required(),
            ColorPicker::make('sidebar_button_vip_background')->label(__('VIP button background color'))->required(),
            ColorPicker::make('sidebar_button_ganhe_background')->label(__('Promotions button background color'))->required(),
            ColorPicker::make('sidebar_button_bonus_background')->label(__('Bonus button background color'))->required(),
            ColorPicker::make('sidebar_button_missoes_text')->label(__('Missions button text color'))->required(),
            ColorPicker::make('sidebar_button_ganhe_text')->label(__('Earn button text color'))->required(),
            ColorPicker::make('sidebar_button_vip_text')->label(__('VIP button text color'))->required(),
            ColorPicker::make('sidebar_button_hover')->label(__('Button hover color'))->required(),
            ColorPicker::make('sidebar_text_hover')->label(__('Text hover color'))->required(),
            ColorPicker::make('sidebar_text_color')->label(__('Text color'))->required(),
            ColorPicker::make('sidebar_border')->label(__('Border color'))->required(),
            ColorPicker::make('sidebar_icons')->label(__('Icons color'))->required(),
                ColorPicker::make('sidebar_icons_background')->label(__('Icons background color'))->required(),
            ])->columns(['default' => 4]);
    }
    // PAGINA HOMEPAGE | FICA NA PAGINA INICIAL DO SITE

    protected function css_do_homepage(): Section
    {
        return Section::make(__('Start Page'))
            ->description(__('You can change the colors of the HomePage'))
            ->label(__('HomePage'))
            ->schema([
                ColorPicker::make('home_text_color')->label(__('Home text color'))->required(),
                ColorPicker::make('home_background')->label(__('Home background color'))->required(),
                ColorPicker::make('home_background_button_banner')->label(__('Banner button background color'))->required(),
                ColorPicker::make('home_icon_color_button_banner')->label(__('Banner button icon color'))->required(),
                
                ColorPicker::make('home_background_input_pesquisa')->label(__('Search input background color'))->required(),
                ColorPicker::make('home_icon_color_input_pesquisa')->label(__('Search input icon color'))->required(),
                ColorPicker::make('home_border_color_input_pesquisa')->label(__('Search input border color'))->required(),

                ColorPicker::make('topo_icon_color')->label(__('Back to top button icon color'))->required(),
                ColorPicker::make('topo_button_text_color')->label(__('Back to top button text color'))->required(),
                ColorPicker::make('topo_button_background')->label(__('Back to top button background color'))->required(),
                ColorPicker::make('topo_button_border_color')->label(__('Back to top button border color'))->required(),


                ColorPicker::make('home_background_categorias')->label(__('Categories background color'))->required(),
                ColorPicker::make('home_text_color_categorias')->label(__('Categories text color'))->required(),
                ColorPicker::make('home_background_pesquisa')->label(__('Search background color'))->required(),
                ColorPicker::make('home_text_color_pesquisa')->label(__('Search text color'))->required(),
                ColorPicker::make('home_background_pesquisa_aviso')->label(__('Search notification background color'))->required(),
                ColorPicker::make('home_text_color_pesquisa_aviso')->label(__('Search notification text color'))->required(),
                ColorPicker::make('home_background_button_pesquisa')->label(__('Search close button background color'))->required(),
                ColorPicker::make('home_icon_color_button_pesquisa')->label(__('Search close button icon color'))->required(),
                ColorPicker::make('home_background_button_vertodos')->label(__('View All button background color'))->required(),
                ColorPicker::make('home_text_color_button_vertodos')->label(__('View All button text color'))->required(),
                ColorPicker::make('home_background_button_jogar')->label(__('Play button background color'))->required(),
                ColorPicker::make('home_text_color_button_jogar')->label(__('Play button text color'))->required(),
                ColorPicker::make('home_icon_color_button_jogar')->label(__('Play button icon color'))->required(),
                ColorPicker::make('home_hover_jogar')->label(__('Play button hover color'))->required(),
            ])->columns(['default' => 4]);
    }

    // PAGINA Maiores Ganhos
    protected function css_dos_maiores_ganhos(): Section
    {
        return Section::make(__('Biggest Wins'))
            ->description(__('You can change the colors and image of the Biggest Wins section'))
            ->label(__('Biggest Wins'))
            ->schema([
        Toggle::make('maiores_ganhos_status')
            ->label(__('Enable Biggest Wins'))
            ->onColor('success')
            ->offColor('danger')
            ->inline(false)
            ->default(false)
            ->reactive(),
                ColorPicker::make('maiores_ganhos_backgroud')->label(__('Background Color'))->required(),
                ColorPicker::make('maiores_ganhos_sub_ackgroud')->label(__('Secondary Background Color'))->required(),
                ColorPicker::make('maiores_ganhos_texto_color')->label(__('Text Color'))->required(),
                ColorPicker::make('maiores_ganhos_valor_color')->label(__('Value Color'))->required(),
                FileUpload::make('maiores_ganhos_img_icon')->label(__('Biggest Wins Icon'))->image()->placeholder(__('Upload an image')),
            ])->columns(['default' => 3]);
    }

    // PAGINA Lives Ganhos  
    protected function css_dos_lives_ganhos(): Section
    {
        return Section::make(__('Live Wins'))
            ->description(__('You can change the colors of the Live Wins section'))
            ->label(__('Live Wins'))
            ->schema([
                Toggle::make('live_ganhos_status')
                    ->label(__('Enable Live Wins'))
                    ->onColor(color: 'success')
                    ->offColor('danger')
                    ->inline(false)
                    ->default(false)
                    ->reactive(),
                ColorPicker::make('live_ganhos_backgroud')->label(__('Background Color'))->required(),
                ColorPicker::make('live_ganhos_sub_backgroud')->label(__('Secondary Background Color'))->required(),
                ColorPicker::make('live_ganhos_texto_color')->label(__('Text Color'))->required(),
                ColorPicker::make('live_ganhos_apostas_color')->label(__('Bets Color'))->required(),
                ColorPicker::make('live_ganhos_ganhos_color')->label(__('Wins Color'))->required(),
                ColorPicker::make('live_ganhos_border_color')->label(__('Border Color'))->required(),
                ColorPicker::make('live_ganhos_box_shadow_color')->label(__('Shadow Color'))->required(),
            ])->columns(['default' => 3]);
    }

    // POP-UP de Rodadas Grátis
    protected function css_do_rodadas_gratis(): Section
    {
        return Section::make(__('Free Rounds Pop-up'))
            ->description(__('Settings for the Free Rounds Pop-up'))
            ->label(__('Free Rounds'))
            ->schema([
                Toggle::make('rodadas_gratis_status')
                    ->label(__('Enable Free Rounds Pop-up'))
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false)
                    ->default(false)
                    ->reactive(),
                FileUpload::make('rodadas_gratis_imagem')->label(__('Pop-up Image 1536 × 1024'))->image()->placeholder(__('Upload an image')),
                ColorPicker::make('rodadas_gratis_background')->label(__('Background Color'))->required(),
                ColorPicker::make('rodadas_gratis_border_color')->label(__('Border Color'))->required(),

                TextInput::make('rodadas_gratis_titulo_texto')->label(__('Title'))->maxLength(90),
                ColorPicker::make('rodadas_gratis_titulo_color')->label(__('Title Color'))->required(),
                ColorPicker::make('rodadas_gratis_titulo_background')->label(__('Title Background Color'))->required(),

                ColorPicker::make('rodadas_gratis_botao_color')->label(__('Button Text Color'))->required(),
                ColorPicker::make('rodadas_gratis_botao_background')->label(__('Button Background Color'))->required(),

                ColorPicker::make('rodadas_gratis_border_color_tabelas')->label(__('Tables Border Color'))->required(),
                ColorPicker::make('rodadas_gratis_color_texto1')->label(__('Text 1 Color'))->required(),
                ColorPicker::make('rodadas_gratis_color_texto2')->label(__('Text 2 Color'))->required(),

                TextInput::make('rodadas_gratis_tabela1_texto1')->label(__('Table 1 - Text 1'))->maxLength(90),
                TextInput::make('rodadas_gratis_tabela1_texto2')->label(__('Table 1 - Text 2'))->maxLength(90),

                TextInput::make('rodadas_gratis_tabela2_texto1')->label(__('Table 2 - Text 1'))->maxLength(90),
                TextInput::make('rodadas_gratis_tabela2_texto2')->label(__('Table 2 - Text 2'))->maxLength(90),

                TextInput::make('rodadas_gratis_tabela3_texto1')->label(__('Table 3 - Text 1'))->maxLength(90),
                TextInput::make('rodadas_gratis_tabela3_texto2')->label(__('Table 3 - Text 2'))->maxLength(90),
            ])->columns(['default' => 3]);
    }
    // PAGINA FOOTER   | FICA EM BAIXO DO SITE

    protected function css_do_footer(): Section
    {

        return Section::make(__('Footer Page'))
            ->description(__('You can change the colors of the footer'))
            ->label(__('Footer'))
            ->schema([
            ColorPicker::make('footer_background')->label(__('Footer background color'))->required(),
            ColorPicker::make('footer_text_color')->label(__('Footer text color'))->required(),
            ColorPicker::make('footer_links')->label(__('Footer links color'))->required(),
            ColorPicker::make('footer_button_background')->label(__('Footer button background color'))->required(),
            ColorPicker::make('footer_button_text')->label(__('Footer button text color'))->required(),
            ColorPicker::make('footer_button_border')->label(__('Footer button border color'))->required(),
            ColorPicker::make('footer_icons')->label(__('Footer icons color'))->required(),
            ColorPicker::make('footer_titulos')->label(__('Footer titles color'))->required(),
            ColorPicker::make('footer_button_hover_language')->label(__('Footer button hover color (Language)'))->required(),
            ColorPicker::make('footer_button_color_language')->label(__('Footer button text color (Language)'))->required(),
            ColorPicker::make('footer_button_background_language')->label(__('Footer button background color (Language)'))->required(),
            ColorPicker::make('footer_button_border_language')->label(__('Footer button border color (Language)'))->required(),
                ColorPicker::make('footer_background_language')->label(__('Footer background color (Language)'))->required(),
            ])->columns(['default' => 4]);
    }
    // PAGINA DE TERMOS E SPORT
    protected function css_do_termos_sport(): Section
    {
        return Section::make(__('Terms and Sports Page'))
            ->description(__('You can change the colors of the Terms and Sports page'))
            ->label(__('Terms and Sports'))
            ->schema([
                ColorPicker::make('aviso_sport_background')->label(__('Sports warning background color'))->required(),
                ColorPicker::make('aviso_sport_text_color')->label(__('Sports warning text color'))->required(),
                ColorPicker::make('titulo_principal_termos')->label(__('Terms main title color'))->required(),
                ColorPicker::make('titulo_segundario_termos')->label(__('Terms secondary title color'))->required(),
                ColorPicker::make('descriçao_segundario_termos')->label(__('Terms secondary description color'))->required(),
            ])->columns(['default' => 2]);
    }


    // Modal MINHA CONTA | FICA NA PAGINA DE MINHA CONTA
    protected function css_do_myconta(): Section
    {
        return Section::make(__('My Account Modal'))
            ->description(__('You can change the colors of the My Account page'))
            ->label(__('My Account'))
            ->schema([
                ColorPicker::make('myconta_background')->label(__('My Account background color'))->required(),
                ColorPicker::make('myconta_sub_background')->label(__('My Account secondary background color'))->required(),
                ColorPicker::make('myconta_text_color')->label(__('My Account text color'))->required(),
                ColorPicker::make('myconta_button_background')->label(__('My Account button background color'))->required(),
                ColorPicker::make('myconta_button_icon_color')->label(__('My Account button icon color'))->required(),
            ])->columns(['default' => 2]);
    }

    // PAGINA CENTRAL SUPORTE | FICA NA PAGINA DE CENTRAL DE SUPORTE
    protected function central_suporte(): Section
    {
        return Section::make(__('Support Center'))
            ->description(__('You can change the colors and styles of the Support Center'))
            ->schema([
                ColorPicker::make('central_suporte_background')->label(__('Support Center Background'))->required(),
                ColorPicker::make('central_suporte_sub_background')->label(__('Secondary Background'))->required(),
                ColorPicker::make('central_suporte_button_background_ao_vivo')->label(__('Live Button Background'))->required(),
                ColorPicker::make('central_suporte_button_texto_ao_vivo')->label(__('Live Button Text'))->required(),
                ColorPicker::make('central_suporte_button_icon_ao_vivo')->label(__('Live Button Icon'))->required(),
                ColorPicker::make('central_suporte_button_background_denuncia')->label(__('Report Button Background'))->required(),
                ColorPicker::make('central_suporte_button_texto_denuncia')->label(__('Report Button Text'))->required(),
                ColorPicker::make('central_suporte_button_icon_denuncia')->label(__('Report Button Icon'))->required(),
                ColorPicker::make('central_suporte_title_text_color')->label(__('Title Color'))->required(),
                ColorPicker::make('central_suporte_icon_title_text_color')->label(__('Title Icon Color'))->required(),
                ColorPicker::make('central_suporte_info_text_color')->label(__('Information Text Color'))->required(),
                ColorPicker::make('central_suporte_info_icon_color')->label(__('Information Icon Color'))->required(),
                ColorPicker::make('central_suporte_aviso_title_color')->label(__('Warning Title Color'))->required(),
                ColorPicker::make('central_suporte_aviso_text_color')->label(__('Warning Text Color'))->required(),
                ColorPicker::make('central_suporte_aviso_text_negrito_color')->label(__('Warning Bold Text Color'))->required(),
                ColorPicker::make('central_suporte_aviso_icon_color')->label(__('Warning Icon Color'))->required(),
            ])->columns(['default' => 2]);
    }
    // PAGINA LOGIN - RESGISTRO E ESQUECI A SENHA

    protected function css_do_login_registro_esquci(): Section
    {
        return Section::make(__('Login, Register and Forgot Password Page'))
            ->description(__('You can change the colors of the Login, Register and Forgot Password pages'))
            ->label(__('Login, Register and Forgot Password'))
            ->schema([
                ColorPicker::make('register_background')->label(__('Registration Background'))->required(),
                ColorPicker::make('register_text_color')->label(__('Registration Text Color'))->required(),
                ColorPicker::make('register_links_color')->label(__('Registration Links Color'))->required(),
                ColorPicker::make('register_input_background')->label(__('Registration Input Background'))->required(),
                ColorPicker::make('register_input_text_color')->label(__('Registration Input Text Color'))->required(),
                ColorPicker::make('register_input_border_color')->label(__('Registration Input Border Color'))->required(),
                ColorPicker::make('register_button_text_color')->label(__('Registration Button Text Color'))->required(),
                ColorPicker::make('register_button_background')->label(__('Registration Button Background'))->required(),
                ColorPicker::make('register_button_border_color')->label(__('Registration Button Border Color'))->required(),
                ColorPicker::make('register_security_color')->label(__('Registration Security Block Color'))->required(),
                ColorPicker::make('register_security_background')->label(__('Registration Security Block Background'))->required(),
                ColorPicker::make('register_security_border_color')->label(__('Registration Security Block Border Color'))->required(),
                ColorPicker::make('geral_icons_color')->label(__('General Icons Color'))->required(),



                ColorPicker::make('login_background')->label(__('Login Background'))->required(),
                ColorPicker::make('login_text_color')->label(__('Login Text Color'))->required(),
                ColorPicker::make('login_links_color')->label(__('Login Links Color'))->required(),
                ColorPicker::make('login_input_background')->label(__('Login Input Background'))->required(),
                ColorPicker::make('login_input_text_color')->label(__('Login Input Text Color'))->required(),
                ColorPicker::make('login_input_border_color')->label(__('Login Input Border Color'))->required(),
                ColorPicker::make('login_button_text_color')->label(__('Login Button Text Color'))->required(),
                ColorPicker::make('login_button_background')->label(__('Login Button Background'))->required(),
                ColorPicker::make('login_button_border_color')->label(__('Login Button Border Color'))->required(),

                ColorPicker::make('esqueci_background')->label(__('Forgot Password Background'))->required(),
                ColorPicker::make('esqueci_text_color')->label(__('Forgot Password Text Color'))->required(),
                ColorPicker::make('esqueci_input_background')->label(__('Forgot Password Input Background'))->required(),
                ColorPicker::make('esqueci_input_text_color')->label(__('Forgot Password Input Text Color'))->required(),
                ColorPicker::make('esqueci_input_border_color')->label(__('Forgot Password Input Border Color'))->required(),
                ColorPicker::make('esqueci_button_text_color')->label(__('Forgot Password Button Text Color'))->required(),
                ColorPicker::make('esqueci_button_background')->label(__('Forgot Password Button Background'))->required(),
                ColorPicker::make('esqueci_button_border_color')->label(__('Forgot Password Button Border Color'))->required(),
            ])->columns(['default' => 4]);
    }


    // PAGINA LISTGAME | FICA NA PAGINA DE LISTA DE JOGOS

    protected function css_do_listgames(): Section
    {
        return Section::make(__('Game List Page'))
            ->description(__('You can change the colors of the Game List'))
            ->label(__('Game List'))
            ->schema([
                ColorPicker::make('gamelist_background')->label(__('Game List Background'))->required(),
                ColorPicker::make('gamelist_input_background')->label(__('Game List Input Background'))->required(),
                ColorPicker::make('gamelist_input_text_color')->label(__('Game List Input Text Color'))->required(),
                ColorPicker::make('gamelist_input_border_color')->label(__('Game List Input Border Color'))->required(),
                ColorPicker::make('gamelist_text_color')->label(__('Game List Text Color'))->required(),
                ColorPicker::make('gamelist_button_background')->label(__('Game List Button Background'))->required(),
                ColorPicker::make('gamelist_button_text_color')->label(__('Game List Button Text Color'))->required(),
                ColorPicker::make('gamelist_button_border_color')->label(__('Game List Button Border Color'))->required(),
            ])->columns(['default' => 4]);
    }

    // PAGINA BONUS DIARIO | FICA NA PAGINA DE BONUS DIARIO

    protected function css_do_bonus_diario(): Section
    {
        return Section::make(__('Daily Bonus Page'))
            ->description(__('You can change the colors of the Daily Bonus page'))
            ->label(__('Daily Bonus'))
            ->schema([
                ColorPicker::make('bonus_diario_background')->label(__('Daily Bonus Background'))->required(),
                ColorPicker::make('bonus_diario_sub_background')->label(__('Daily Bonus Secondary Background'))->required(),
                ColorPicker::make('bonus_diario_texto')->label(__('Daily Bonus Text Color'))->required(),
                ColorPicker::make('bonus_diario_texto_icon')->label(__('Daily Bonus Text Icon Color'))->required(),
                ColorPicker::make('bonus_diario_texto_valor_bonus')->label(__('Daily Bonus Text Bonus Value Color'))->required(),
                ColorPicker::make('bonus_diario_aviso')->label(__('Daily Bonus Warning Color'))->required(),
                ColorPicker::make('bonus_diario_botao_backgroud')->label(__('Daily Bonus Button Background'))->required(),
                ColorPicker::make('bonus_diario_botao_texto_color')->label(__('Daily Bonus Button Text Color'))->required(),
                ColorPicker::make('bonus_diario_regras_title')->label(__('Daily Bonus Rules Title Color'))->required(),
                ColorPicker::make('bonus_diario_regras_titulo')->label(__('Daily Bonus Rules Heading Color'))->required(),
                ColorPicker::make('bonus_diario_bola_interna')->label(__('Daily Bonus Internal Ball Color'))->required(),
                ColorPicker::make('bonus_diario_bola_fora_')->label(__('Daily Bonus External Ball Color'))->required(),
                ColorPicker::make('bonus_diario_bola_carregamento')->label(__('Daily Bonus Loading Ball Color'))->required(),
                ColorPicker::make('bonus_diario_texto_bola')->label(__('Daily Bonus Ball Text Color'))->required(),
            ])->columns(['default' => 4]);
    }





    /////////////////////////////////////////////////////////////////////
    ////////////////////// CENTRAL DE DISIGN PT2 ////////////////////////
    /////////////////////////////////////////////////////////////////////

    // PAGINA CARTERA | FICA NA PAGINA DE CARTEIRA


    protected function css_do_WalletDashboard(): Section
    {
        return Section::make(__('Wallet Dashboard'))
            ->description(__('You can change the colors of the Wallet Dashboard'))
            ->label(__('Wallet Dashboard'))
            ->schema([
                ColorPicker::make('carteira_background')->label(__('Wallet Background Color'))->required(),
                ColorPicker::make('carteira_button_background')->label(__('Wallet Button Background Color'))->required(),
                ColorPicker::make('carteira_button_text_color')->label(__('Wallet Button Text Color'))->required(),
                ColorPicker::make('carteira_button_border_color')->label(__('Wallet Button Border Color'))->required(),
                ColorPicker::make('carteira_icon_color')->label(__('Wallet Icons Color'))->required(),
                ColorPicker::make('carteira_text_color')->label(__('Wallet Text Color'))->required(),
            ])->columns(['default' => 4]);
    }



    protected function css_do_WalletBalance(): Section
    {
        return Section::make(__('Wallet Balance'))
            ->description(__('You can change the colors of the Wallet Balance'))
            ->label(__('Wallet Balance'))
            ->schema([
                ColorPicker::make('carteira_saldo_background')->label(__('Wallet Balance Background color'))->required(),
                ColorPicker::make('carteira_saldo_text_color')->label(__('Wallet Balance Text color'))->required(),
                ColorPicker::make('carteira_saldo_border_color')->label(__('Wallet Balance Border color'))->required(),
                ColorPicker::make('carteira_saldo_title_color')->label(__('Wallet Balance Title color'))->required(),
                ColorPicker::make('carteira_saldo_icon_color')->label(__('Wallet Balance Icons color'))->required(),
                ColorPicker::make('carteira_saldo_number_color')->label(__('Wallet Balance Numbers color'))->required(),
                ColorPicker::make('carteira_saldo_button_deposito_background')->label(__('Wallet Balance Deposit Button Background color'))->required(),
                ColorPicker::make('carteira_saldo_button_saque_background')->label(__('Wallet Balance Withdrawal Button Background color'))->required(),
                ColorPicker::make('carteira_saldo_button_deposito_text_color')->label(__('Wallet Balance Deposit Button Text color'))->required(),
                ColorPicker::make('carteira_saldo_button_saque_text_color')->label(__('Wallet Balance Withdrawal Button Text color'))->required(),
            ])->columns(['default' => 3]);
    }



    protected function css_do_WalletDeposit(): Section
    {
        return Section::make(__('Wallet Deposit'))
            ->description(__('You can change the colors of the Wallet Deposit'))
            ->label(__('Wallet Deposit'))
            ->schema([
                ColorPicker::make('carteira_deposito_background')->label(__('Wallet Deposit Background Color'))->required(),
                ColorPicker::make('carteira_deposito_contagem_background')->label(__('Wallet Deposit Count Background Color'))->required(),
                ColorPicker::make('carteira_deposito_contagem_text_color')->label(__('Wallet Deposit Count Text Color'))->required(),
                ColorPicker::make('carteira_deposito_contagem_number_color')->label(__('Wallet Deposit Count Number Color'))->required(),
                ColorPicker::make('carteira_deposito_contagem_quadrado_background')->label(__('Wallet Deposit Count Box Background Color'))->required(),
                ColorPicker::make('carteira_deposito_input_background')->label(__('Wallet Deposit Input Background Color'))->required(),
                ColorPicker::make('carteira_deposito_input_text_color')->label(__('Wallet Deposit Input Text Color'))->required(),
                ColorPicker::make('carteira_deposito_input_number_color')->label(__('Wallet Deposit Input Number Color'))->required(),
                ColorPicker::make('carteira_deposito_input_border_color')->label(__('Wallet Deposit Input Border Color'))->required(),
                ColorPicker::make('carteira_deposito_title_color')->label(__('Wallet Deposit Title Color'))->required(),
                ColorPicker::make('carteira_deposito_number_color')->label(__('Wallet Deposit Number Color'))->required(),
                ColorPicker::make('carteira_deposito_number_background')->label(__('Wallet Deposit Number Background Color'))->required(),
                ColorPicker::make('carteira_deposito_button_background')->label(__('Wallet Deposit Button Background Color'))->required(),
                ColorPicker::make('carteira_deposito_button_text_color')->label(__('Wallet Deposit Button Text Color'))->required(),
            ])->columns(['default' => 3]);
    }




    protected function css_do_CryptoWallet(): Section
    {
        return Section::make(__('Crypto Wallet Copy and Paste'))
            ->description(__('You can change the colors of the Crypto Wallet'))
            ->label(__('Crypto Wallet'))
            ->schema([
                ColorPicker::make('carteira_saldo_pix_Texot_color')->label(__('Wallet Crypto Balance Text Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_sub_text_color')->label(__('Wallet Crypto Copy Subtext Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_button_background')->label(__('Wallet Crypto Copy Button Background Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_button_text_color')->label(__('Wallet Crypto Copy Button Text Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_input_background')->label(__('Wallet Crypto Input Background Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_input_text_color')->label(__('Wallet Crypto Input Text Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_border_color')->label(__('Wallet Crypto Copy Border Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_icon_color')->label(__('Wallet Crypto Copy Icon Color'))->required(),
                ColorPicker::make('carteira_saldo_pix_background')->label(__('Wallet Crypto Copy Background Color'))->required(),
            ])->columns(['default' => 2]);
    }






    protected function css_do_WalletWithdrawal(): Section
    {
        return Section::make(__('Wallet Withdrawal'))
            ->description(__('You can change the colors of the Wallet Withdrawal'))
            ->label(__('Wallet Withdrawal'))
            ->schema([
                ColorPicker::make('carteira_saque_background')->label(__('Wallet Withdrawal Background Color'))->required(),
                ColorPicker::make('carteira_saque_text_color')->label(__('Wallet Withdrawal Text Color'))->required(),
                ColorPicker::make('carteira_saque_number_color')->label(__('Wallet Withdrawal Number Color'))->required(),
                ColorPicker::make('carteira_saque_input_background')->label(__('Wallet Withdrawal Input Background Color'))->required(),
                ColorPicker::make('carteira_saque_input_text_color')->label(__('Wallet Withdrawal Input Text Color'))->required(),
                ColorPicker::make('carteira_saque_input_border_color')->label(__('Wallet Withdrawal Input Border Color'))->required(),
                ColorPicker::make('carteira_saque_button_text_color')->label(__('Wallet Withdrawal Button Text Color'))->required(),
                ColorPicker::make('carteira_saque_button_background')->label(__('Wallet Withdrawal Button Background Color'))->required(),
                ColorPicker::make('carteira_saque_icon_color')->label(__('Wallet Withdrawal Icons Color'))->required(),
            ])->columns(['default' => 2]);
    }



    protected function css_do_BetHistory(): Section
    {
        return Section::make(__('Bet History'))
            ->description(__('You can change the colors of the Bet History'))
            ->label(__('Bet History'))
            ->schema([
                ColorPicker::make('carteira_history_background')->label(__('Wallet History Background Color'))->required(),
                ColorPicker::make('carteira_history_title_color')->label(__('Wallet History Title Color'))->required(),
                ColorPicker::make('carteira_history_text_color')->label(__('Wallet History Text Color'))->required(),
                ColorPicker::make('carteira_history_number_color')->label(__('Wallet History Number Color'))->required(),
                ColorPicker::make('carteira_history_status_finalizado_color')->label(__('Wallet History Finalized Status Color'))->required(),
                ColorPicker::make('carteira_history_status_finalizado_background')->label(__('Wallet History Finalized Status Background Color'))->required(),
                ColorPicker::make('carteira_history_status_pedente_color')->label(__('Wallet History Pending Status Color'))->required(),
                ColorPicker::make('carteira_history_status_pedente_background')->label(__('Wallet History Pending Status Background Color'))->required(),
                ColorPicker::make('carteira_history_barra_color')->label(__('Wallet History Bar Color'))->required(),
                ColorPicker::make('carteira_history_barra_text_color')->label(__('Wallet History Bar Text Color'))->required(),
                ColorPicker::make('carteira_history_icon_color')->label(__('Wallet History Icons Color'))->required(),
                ColorPicker::make('carteira_history_barra_background')->label(__('Wallet History Bar Background Color'))->required(),
            ])->columns(['default' => 4]);
    }



    // PAGINA DE AFILIADOS | FICA NA PAGINA DE AFILIADOS


    protected function css_do_affiliates(): Section
    {
        return Section::make(__('Affiliates Page'))
            ->description(__('You can change the colors of the Affiliates page'))
            ->label(__('Affiliates'))
            ->schema([
                ColorPicker::make('affiliates_background')->label(__('Affiliates Background Color'))->required(),
                ColorPicker::make('affiliates_sub_background')->label(__('Affiliates Secondary Background Color'))->required(),
                ColorPicker::make('affiliates_text_color')->label(__('Affiliates Text Color'))->required(),
                ColorPicker::make('affiliates_numero_color')->label(__('Affiliates Number Color'))->required(),
                ColorPicker::make('affiliates_button_saque_background')->label(__('Withdrawal Button Background Color'))->required(),
                ColorPicker::make('affiliates_button_saque_text')->label(__('Withdrawal Button Text Color'))->required(),
                ColorPicker::make('affiliates_button_copie_background')->label(__('Copy Button Background Color'))->required(),
                ColorPicker::make('affiliates_button_copie_text')->label(__('Copy Button Icon Color'))->required(),
                ColorPicker::make('affiliates_icom_color')->label(__('Affiliates Icons Color'))->required(),
            ])->columns(['default' => 4]);
    }

    


    /////////////////////////////////////////////////////////////////////
    ////////////////////// CENTRAL DE DISIGN PT3 ////////////////////////
    /////////////////////////////////////////////////////////////////////

    
    // PAGINA VIP | FICA NA PAGINA DE VIP
    protected function css_do_vips(): Section
    {
        return Section::make(__('VIP Page'))
            ->description(__('You can change the colors of the VIP page'))
            ->label(__('VIP'))
            ->schema([
                ColorPicker::make('vips_background')->label(__('VIPs Background Color'))->required(),
                ColorPicker::make('vips_title_color')->label(__('VIPs Title Color'))->required(),
                ColorPicker::make('vips_text_color')->label(__('VIPs Text Color'))->required(),
                ColorPicker::make('vips_description_color')->label(__('VIPs Description Color'))->required(),
                ColorPicker::make('vips_button_background')->label(__('VIPs Button Background Color'))->required(),
                ColorPicker::make('vips_button_text_color')->label(__('VIPs Button Text Color'))->required(),
                ColorPicker::make('vips_progress_background')->label(__('VIPs Progress Background Color'))->required(),
                ColorPicker::make('vips_progress_text_color')->label(__('VIPs Progress Text Color'))->required(),
                ColorPicker::make('vips_progress_prenchimento_background')->label(__('VIPs Progress Fill Color'))->required(),
                ColorPicker::make('vips_icons_text_color')->label(__('VIPs Icons Text Color'))->required(),
                ColorPicker::make('vips_icons_background')->label(__('VIPs Icons Background Color'))->required(),
                ColorPicker::make('vips_icons_sub_text_color')->label(__('VIPs Icons Subtext Color'))->required(),
                ColorPicker::make('vips_background_profile_vip')->label(__('VIP Profile Background Color'))->required(),
                ColorPicker::make('vips_icon_mover_color')->label(__('VIPs Move Icon Color'))->required(),
                ColorPicker::make('vip_atual_background')->label(__('Current VIP Background Color'))->required(),
                ColorPicker::make('vip_atual_text_color')->label(__('Current VIP Text Color'))->required(),
                ColorPicker::make('vip_proximo_background')->label(__('Next VIP Background Color'))->required(),
                ColorPicker::make('vip_proximo_text_color')->label(__('Next VIP Text Color'))->required(),
            ])->columns(['default' => 4]);
    }


    

    // PAGINA DE MISSOES | FICA NA PAGINA DE MISSOES
    protected function css_do_missoes(): Section
    {
        return Section::make(__('Missions Page'))
            ->description(__('You can change the colors of the Missions page'))
            ->label(__('Missions'))
            ->schema([
                ColorPicker::make('missoes_background')->label(__('Missions Background Color'))->required(),
                ColorPicker::make('missoes_sub_background')->label(__('Missions Secondary Background Color'))->required(),
                ColorPicker::make('missoes_text_color')->label(__('Missions Text Color'))->required(),
                ColorPicker::make('missoes_title_color')->label(__('Missions Title Color'))->required(),
                ColorPicker::make('missoes_bonus_background')->label(__('Missions Bonus Background Color'))->required(),
                ColorPicker::make('missoes_bonus_text_color')->label(__('Missions Bonus Text Color'))->required(),
                ColorPicker::make('missoes_button_background')->label(__('Missions Button Background Color'))->required(),
                ColorPicker::make('missoes_button_text_color')->label(__('Missions Button Text Color'))->required(),
                ColorPicker::make('missoes_barraprogresso_background')->label(__('Missions Progress Bar Background Color'))->required(),
                ColorPicker::make('missoes_barraprogresso_prenchimento_background')->label(__('Missions Progress Bar Fill Color'))->required(),
                ColorPicker::make('missoes_barraprogresso_text_color')->label(__('Missions Progress Bar Text Color'))->required(),
            ])->columns(['default' => 4]);
    }

    

    // PAGINA DE PROMOÇÕES | FICA NA PAGINA DE PROMOÇÕES
    protected function css_do_promocoes(): Section
    {
        return Section::make(__('Promotions Page'))
            ->description(__('You can change the colors of the Promotions page'))
            ->label(__('Promotions'))
            ->schema([
                ColorPicker::make('promocoes_background')->label(__('Promotions Background Color'))->required(),
                ColorPicker::make('promocoes_title_color')->label(__('Promotions Title Color'))->required(),
                ColorPicker::make('promocoes_text_color')->label(__('Promotions Text Color'))->required(),
                ColorPicker::make('promocoes_sub_background')->label(__('Promotions Secondary Background Color'))->required(),
                ColorPicker::make('promocoes_button_background')->label(__('Promotions Button Background Color'))->required(),
                ColorPicker::make('promocoes_button_text_color')->label(__('Promotions Button Text Color'))->required(),
                ColorPicker::make('promocoes_pupup_background')->label(__('Promotions Popup Background Color'))->required(),
                ColorPicker::make('promocoes_pupup_text_color')->label(__('Promotions Popup Text Color'))->required(),
                ColorPicker::make('promocoes_icon_color')->label(__('Promotions Icons Color'))->required(),
            ])->columns(['default' => 4]);
    }


    // PAGINA DE POPUP DE COOKIES | FICA NA PAGINA DE POPUP DE COOKIES
    protected function css_do_popup_cookies(): Section
    {
        return Section::make(__('Cookie Popup'))
            ->description(__('You can change the colors of the Cookie Popup'))
            ->label(__('Cookie Popup'))
            ->schema([
                ColorPicker::make('popup_cookies_background')->label(__('Cookie Popup Background Color'))->required(),
                ColorPicker::make('popup_cookies_text_color')->label(__('Cookie Popup Text Color'))->required(),
                ColorPicker::make('popup_cookies_button_background')->label(__('Cookie Popup Button Background Color'))->required(),
                ColorPicker::make('popup_cookies_button_text_color')->label(__('Cookie Popup Button Text Color'))->required(),
                ColorPicker::make('popup_cookies_button_border_color')->label(__('Cookie Popup Button Border Color'))->required(),
            ])->columns(['default' => 2]);
    }

    // PAGINA DE MENU CELL | FICA NA PAGINA DE MENU CELULAR
    protected function css_do_menu_cell(): Section
    {
        return Section::make(__('Mobile Menu Page'))
            ->description(__('You can change the colors of the Mobile Menu'))
            ->label(__('Mobile Menu'))
            ->schema([
                ColorPicker::make('menu_cell_background')->label(__('Mobile Menu Background Color'))->required(),
                ColorPicker::make('menu_cell_text_color')->label(__('Mobile Menu Text Color'))->required(),
            ])->columns(['default' => 2]);
    }




    // GERAL
    protected function css_do_geral(): Section
    {
        return Section::make(__('General Settings'))
            ->description(__('You can change general colors'))
            ->label(__('General'))
            ->schema([
                ColorPicker::make('background_geral')->label(__('General Background Color'))->required(),
                ColorPicker::make('background_geral_text_color')->label(__('General Text Color'))->required(),
            ])->columns(['default' => 2]);
    }

    // CARREGANDO
    protected function css_do_carregando(): Section
    {
        return Section::make(__('Loading Screen'))
            ->description(__('You can change the colors of the loading screen'))
            ->label(__('Loading'))
            ->schema([
                ColorPicker::make('carregando_background')->label(__('Loading Background Color'))->required(),
                ColorPicker::make('carregando_text_color')->label(__('Loading Text Color'))->required(),
            ])->columns(['default' => 2]);
    }

    /////////////////////////////////////////////////////////////////////
    ////////////////////// CENTRAL DE DISIGN PT4 ////////////////////////
    /////////////////////////////////////////////////////////////////////

    // PAGINA DE TERMOS E CONDIÇÕES | FICA NA PAGINA DE TERMOS E CONDIÇÕES

    // PAGINA DE POLITICA DE PRIVACIDADE | FICA NA PAGINA DE POLITICA DE PRIVACIDADE

    // PAGINA DE POLITICA DE COOKIES | FICA NA PAGINA DE POLITICA DE COOKIES

    // PAGINA DE TERMO DE BONUS | FICA NA PAGINA DE TERMO DE BONUS



    /////////////////////////////////////////////////////////////////////

    protected function css_do_maior_de_18(): Section
    {
        return Section::make(__('Over 18 Popup'))
            ->description(__('You can change colors for the age confirmation popup'))
            ->label(__('Over 18'))
            ->schema([
                Toggle::make('maior_de_18_status')
                    ->label(__('Enable Over 18 Popup'))
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false)
                    ->default(false)
                    ->reactive(),
                ColorPicker::make('maior_de_18_background')->label(__('Popup Background Color'))->required(),
                ColorPicker::make('maior_de_18_sub_background')->label(__('Secondary Background Color'))->required(),
                ColorPicker::make('maior_de_18_texto_color')->label(__('Text Color'))->required(),
                ColorPicker::make('maior_de_18_botao_sim_background')->label(__('Background color for "Yes" button'))->required(),
                ColorPicker::make('maior_de_18_botao_sim_texto_color')->label(__('Text color for "Yes" button'))->required(),
                ColorPicker::make('maior_de_18_botao_nao_background')->label(__('Background color for "No" button'))->required(),
                ColorPicker::make('maior_de_18_botao_nao_texto_color')->label(__('Text color for "No" button'))->required(),
            ])->columns(['default' => 2]);
    }

    /////////////////////////////////////////////////////////////////////
    ////////////////////// CENTRAL DE TEXTOS ////////////////////////
    /////////////////////////////////////////////////////////////////////
    protected function getSectionPlatformTexts(): Section
    {
        return Section::make(__('PLATFORM TEXTS'))
            ->label(__('Change platform texts'))
            ->schema([
                TextInput::make('homepage_jogos_em_destaque')->label(__('Featured Games on Homepage')),
                TextInput::make('vip_titulo')->label(__('VIP Title')),
                TextInput::make('vip_descriçao')->label(__('VIP Description')),
                TextInput::make('vip_sub_texto')->label(__('VIP Subtext')),
                TextInput::make('vip_sub_titulo')->label(__('VIP Subtitle')),
            ])->columns(['default' => 2]);
    }





    public function submit(): void
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Attention'))
                    ->body(__('You cannot perform this change in the demo version'))
                    ->danger()
                    ->send();
                return;
            }

            $data = $this->form->getState();
            $this->handleFileUploads();

            if ($this->custom->update($data)) {

                // 1) Limpa caches do backend (sem mexer em sessões/filas)
                /** @var CacheNuker $nuker */
                $nuker = app(CacheNuker::class);
                $nuker->run([
                    'deep'     => true,   // apaga views/cache/data/bootstrap etc.
                    'sessions' => false,  // mantém logins
                    'queues'   => false,  // mantém filas
                ]);

                // 2) Cache-bust global pros ETags (front baixa CSS/JS novo)
                Cache::forever('asset_version', (string) Str::uuid());

                Notification::make()
                    ->title(__('Customization saved'))
                    ->body(__('Data changed and cache cleared successfully!'))
                    ->success()
                    ->send();

                return;
            }

            Notification::make()
                ->title(__('Error'))
                ->body(__('Internal error!'))
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('Error changing data!'))
                ->body(__('Error changing data!'))
                ->danger()
                ->send();
        }
    }


    private function handleFileUploads(): void
    {
        $this->data['image_hot4'] = $this->processFileUpload($this->data['image_hot4']);
        $this->data['rodadas_gratis_imagem'] = $this->processFileUpload($this->data['rodadas_gratis_imagem']);
        $this->data['banner_deposito1'] = $this->processFileUpload($this->data['banner_deposito1']);
        $this->data['banner_deposito2'] = $this->processFileUpload($this->data['banner_deposito2']);
        $this->data['banner_registro'] = $this->processFileUpload($this->data['banner_registro']);
        $this->data['banner_login'] = $this->processFileUpload($this->data['banner_login']);
        $this->data['menucell_inicio'] = $this->processFileUpload($this->data['menucell_inicio']);
        $this->data['menucell_carteira'] = $this->processFileUpload($this->data['menucell_carteira']);
        $this->data['menucell_suporte'] = $this->processFileUpload($this->data['menucell_suporte']);
        $this->data['menucell_afiliado'] = $this->processFileUpload($this->data['menucell_afiliado']);
        $this->data['menucell_saque'] = $this->processFileUpload($this->data['menucell_saque']);
        $this->data['menucell_sair'] = $this->processFileUpload($this->data['menucell_sair']);
        $this->data['footer_imagen1'] = $this->processFileUpload($this->data['footer_imagen1']);
        $this->data['menucell_img_esportes'] = $this->processFileUpload($this->data['menucell_img_esportes']);
        $this->data['footer_imagen2'] = $this->processFileUpload($this->data['footer_imagen2']);
        $this->data['footer_imagen3'] = $this->processFileUpload($this->data['footer_imagen3']);
        $this->data['footer_telegram'] = $this->processFileUpload($this->data['footer_telegram']);
        $this->data['footer_facebook'] = $this->processFileUpload($this->data['footer_facebook']);
        $this->data['footer_whatsapp'] = $this->processFileUpload($this->data['footer_whatsapp']);
        $this->data['footer_instagram'] = $this->processFileUpload($this->data['footer_instagram']);
        $this->data['footer_mais18'] = $this->processFileUpload($this->data['footer_mais18']);
        $this->data['maiores_ganhos_img_icon'] = $this->processFileUpload($this->data['maiores_ganhos_img_icon']);
    }
    
    
    private function processFileUpload($file)
    {
        // Se não houver arquivo (ou for null), retorna null sem tentar processar.
        if (!$file) {
            return null;
        }
    
        // Se o upload existir, processa o arquivo.
        return $this->uploadFile($file);
    }
}
