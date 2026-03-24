<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CupomResource\Pages;
use App\Models\Cupom;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CupomResource extends Resource
{
    protected static ?string $model = Cupom::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';  // Ícone de navegação apropriado para cupons

    protected static ?string $label = 'Coupon';
    protected static ?string $pluralLabel = 'Bonus Coupons';  

    protected static ?string $navigationLabel = 'COUPON SETTINGS';  

    protected static ?string $navigationGroup = 'Promoções';  // Agrupado na seção de Promoções

    /**
     * Controla o acesso ao recurso
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
                Section::make('COUPON INFORMATION')
                ->description(new HtmlString('
                    <div style="font-weight: 600;">
                        Crie e gerencie os cupons de bônus disponíveis para os usuários.
                    </div>
                ')),
                Forms\Components\Section::make()
                    ->schema([

                        Forms\Components\TextInput::make('codigo')
                            ->label(__('Coupon Code'))
                            ->placeholder(__('Enter the coupon code'))
                            ->required()
                            ->maxLength(191),

                        Forms\Components\TextInput::make('valor_bonus')
                            ->label(__('Bonus Amount'))
                            ->placeholder(__('Enter the bonus amount'))
                            ->numeric()
                            ->required(),

                        Forms\Components\DatePicker::make('validade')
                            ->label(__('Expiration'))
                            ->placeholder(__('Choose the coupon expiration date'))
                            ->required(),

                        Forms\Components\TextInput::make('quantidade_uso')
                            ->label(__('Use Quantity'))
                            ->placeholder(__('Enter the maximum number of uses'))
                            ->numeric()
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
                Tables\Columns\TextColumn::make('codigo')
                    ->label(__('Code')),

                Tables\Columns\TextColumn::make('valor_bonus')
                    ->label(__('Bonus Amount')),

                Tables\Columns\TextColumn::make('validade')
                    ->label(__('Validade'))
                    ->date(),

                Tables\Columns\TextColumn::make('quantidade_uso')
                    ->label(__('Quantidade de Usos')),

                Tables\Columns\TextColumn::make('usos')
                    ->label(__('Uses'))
            ])
            ->filters([/* Adicionar filtros se necessário */])
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
            // Definir relacionamentos, se necessário
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCupoms::route('/'),    // Mantenha os nomes de páginas como especificado
            'create' => Pages\CreateCupom::route('/create'),
            'edit' => Pages\EditCupom::route('/{record}/edit'),
        ];
    }
}
