<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promocao;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;




class PromotionResource extends Resource
{
    protected static ?string $model = Promocao::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Promotion';
    protected static ?string $pluralLabel = 'Promotions';
    protected static ?string $navigationLabel = 'MANAGE PROMOTIONS';
    protected static ?string $navigationGroup = 'Marketing';

    /**
     * Controla o acesso ao recurso
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Define o formulário para criação/edição
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titulo')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('link')
                    ->label(__('Link'))
                    ->placeholder(__('Enter the promotion link'))
                    ->url()
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('imagem')
                    ->label(__('Image'))
                    ->image()
                    ->required()
                    ->directory('/uploads/promocoes') // Diretório onde as imagens serão salvas
                    ->placeholder(__('Upload an image')),
                Forms\Components\RichEditor::make('regras_html')
                    ->label(__('Rules'))
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'bulletList',
                        'orderedList',
                        'link',
                        'codeBlock',
                    ]),

            ]);
    }

    /**
     * Configura a tabela de exibição no painel
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagem')
                    ->label(__('Image'))
                    ->circular(),
                TextColumn::make('titulo')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('link')
                    ->label(__('Link'))
                    ->url(fn ($record) => $record->link, true)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                // Adicione filtros personalizados se necessário
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

    /**
     * Configura as páginas do recurso
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
