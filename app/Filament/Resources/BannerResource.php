<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Banner';
    protected static ?string $pluralLabel = 'ADD BANNERS TO HOME PAGE';

    protected static ?string $navigationLabel = 'BANNER SETTINGS';

    protected static ?string $navigationGroup = 'Marketing';  // Opcional: para agrupar em uma seção de navegação

    /**
     * @dev 𝓗𝓐𝓡𝓚𝓩𝓘𝓜 / by OndaGames.com < - Esse sistema é Gratuito - Entre no nosso Grupo  https://t.me/+dFr8-1AmUz5hZDc5
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('OBETZERA CREATED THIS PLATFORM FOR YOU')
                    ->description(new HtmlString('
                    <div style="font-weight: 600; display: flex; align-items: center;">
                        FIND OUT MORE ABOUT US. JOIN OUR IGAMING COMMUNITY. ACCESS NOW! 
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
                            OFFICIAL SITE
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
                            TELEGRAM GROUP
                        </a>
                    </div>
                ')),
                Forms\Components\Section::make()
                    ->description('')
                    ->schema([
                        Forms\Components\Group::make()->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\TextInput::make('link')
                                        ->label(__('Link'))
                                        ->placeholder(__('Enter the banner link'))
                                        ->maxLength(191),
                                    Forms\Components\Select::make('type')
                                        ->label(__('Select type'))
                                        ->options([
                                            'carousel' => 'Carousel Banner',
                                            'home' => 'Home Banner',
                                        ])
                                        ->required(),
                                ])->columns(2)->columnSpanFull(),

                            Forms\Components\Textarea::make('description')
                                ->placeholder(__('Enter a description'))
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])->columns(2),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->required(),
                    ])
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('Image')),
                Tables\Columns\TextColumn::make('link')
                    ->label(__('Link')),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
