<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributionSystemResource\Pages;
use App\Models\DistributionSystem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class DistributionSystemResource extends Resource
{
    /**
     * Modelo Eloquent que este recurso gerencia
     */
    protected static ?string $model = DistributionSystem::class;

    /**
     * Customizações de exibição no menu do Filament
     */
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $label = 'Earnings Distribution';
    protected static ?string $pluralLabel = 'Distribution System';
    protected static ?string $navigationLabel = 'DISTRIBUTION SYSTEM';
    protected static ?string $navigationGroup = 'Finance';

    /**
     * Controla o acesso: somente Admin pode ver
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Definição do formulário para edição do registro
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('ativo')
                    ->label(__('System Enabled'))
                    ->helperText('Ligue ou desligue o sistema de distribuição.')
                    ->live(),

                Forms\Components\TextInput::make('meta_arrecadacao')
                    ->label(__('Collection Goal'))
                    ->numeric()
                    ->step(1)
                    ->rules(['integer']),

                Forms\Components\TextInput::make('percentual_distribuicao')
                    ->label(__('% Distribution'))
                    ->numeric()
                    ->step(1)
                    ->rules(['integer']),


                Forms\Components\TextInput::make('rtp_arrecadacao')
                    ->label(__('Collection RTP'))
                    ->numeric()
                    ->step(1)
                    ->rules(['integer']),
                Forms\Components\TextInput::make('rtp_distribuicao')
                    ->label(__('Distribution RTP'))
                    ->numeric()
                    ->step(1)
                    ->rules(['integer']),

                Forms\Components\TextInput::make('total_arrecadado')
                    ->label(__('Total Collected'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('total_distribuido')
                    ->label(__('Total Distributed'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\Select::make('modo')
                    ->label(__('Current Mode'))
                    ->options([
                        'arrecadacao' => 'Arrecadação',
                        'distribuicao' => 'Distribuição',
                    ])
                    ->disabled(),
            ]);
    }

    /**
     * Opcional: Tabela de visualização (caso queira ver o registro),
     * mas sem permitir criação ou exclusão.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('ativo')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => $state ? 'Ativado' : 'Desativado')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('meta_arrecadacao')
                    ->label(__('Collection Goal')),

                Tables\Columns\TextColumn::make('percentual_distribuicao')
                    ->label(__('% Distribution')),

                Tables\Columns\TextColumn::make('rtp_arrecadacao')
                    ->label(__('Collection RTP')),

                Tables\Columns\TextColumn::make('rtp_distribuicao')
                    ->label(__('Distribution RTP')),

                Tables\Columns\TextColumn::make('total_arrecadado')
                    ->label(__('Total Collected')),

                Tables\Columns\TextColumn::make('total_distribuido')
                    ->label(__('Total Distributed')),

                Tables\Columns\BadgeColumn::make('modo')
                    ->label(__('Current Mode'))
                    ->formatStateUsing(fn ($state) => $state === 'arrecadacao' ? 'Arrecadação' : 'Distribuição')
                    ->color(fn ($state) => $state === 'arrecadacao' ? 'primary' : 'success'),
            ])
            ->actions([
                // Só permitimos edição do único registro
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]) // sem ações em lote
            ->defaultPagination(1); // exibe no máximo 1 registro
    }

    /**
     * Importante: forçar a query a retornar somente um registro.
     */
    public static function getEloquentQuery(): Builder
    {
        // limit(1) para não listar múltiplos
        return parent::getEloquentQuery()->limit(1);
    }

    /**
     * Redefine as páginas disponíveis:
     *  - 'index' => redirecionado para a tela de edição
     */
    public static function getPages(): array
    {
        return [
            // Ao acessar /distribution-systems, irá diretamente para EditDistributionSystem
            'index' => Pages\EditDistributionSystem::route('/'),
        ];
    }
}
