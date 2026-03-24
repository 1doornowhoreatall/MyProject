<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MinesConfigResource\Pages;
use App\Models\GameConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MinesConfigResource extends Resource
{
    protected static ?string $model = GameConfig::class;

    protected static ?string $navigationIcon   = 'heroicon-o-ticket';
    protected static ?string $label            = 'Config. Mines';
    protected static ?string $pluralLabel      = 'Config. Mines';
    protected static ?string $navigationLabel  = 'CONFIGURAÇÃO MINES';
    protected static ?string $navigationGroup  = 'Finanças';

    public static function canAccess(): bool
    {
        return auth()->user() && auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campos editáveis
                Forms\Components\TextInput::make('meta_arrecadacao')
                    ->label(__('Meta de Arrecadação'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('percentual_distribuicao')
                    ->label(__('% de Distribuição'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('minas_distribuicao')
                    ->label(__('Minas (Distribuição)'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('minas_arrecadacao')
                    ->label(__('Minas (Arrecadação)'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('x_por_mina')
                    ->label(__('Multiplicador por Mina'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('x_a_cada_5')
                    ->label(__('Acrescimo a cada 5'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('bet_loss')
                    ->label(__('% de Bet Loss'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\Toggle::make('modo_influenciador')
                    ->label(__('Modo Influenciador'))
                    ->helperText('Se ativo, o usuário só ganha.')
                    ->default(false),

                Forms\Components\Toggle::make('modo_perdedor')
                    ->label(__('Modo Perdedor'))
                    ->helperText('Se ativo, o usuário só perde.')
                    ->default(false),

                // Campos apenas informativos (disabled)
                Forms\Components\TextInput::make('modo_atual')
                    ->label(__('Modo Atual'))
                    ->disabled(),

                Forms\Components\TextInput::make('total_arrecadado')
                    ->label(__('Total Arrecadado'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('total_distribuido')
                    ->label(__('Total Distribuído'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\DateTimePicker::make('start_cycle_at')
                    ->label(__('Início do Ciclo'))
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('modo_atual')
                    ->label(__('Modo Atual'))
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'arrecadacao'  => 'Arrecadação',
                            'distribuicao' => 'Distribuição',
                            'influenciador'=> 'Influenciador',
                            'perdedor'     => 'Perdedor',
                            default        => $state,
                        };
                    })
                    ->colors([
                        'primary' => 'arrecadacao',
                        'success' => 'distribuicao',
                        'warning' => 'influenciador',
                        'danger'  => 'perdedor',
                    ]),

                Tables\Columns\TextColumn::make('meta_arrecadacao')
                    ->label(__('Meta de Arrecadação')),
                Tables\Columns\TextColumn::make('percentual_distribuicao')
                    ->label(__('% de Distribuição')),
                Tables\Columns\TextColumn::make('minas_distribuicao')
                    ->label(__('Minas (Distribuição)')),
                Tables\Columns\TextColumn::make('minas_arrecadacao')
                    ->label(__('Minas (Arrecadação)')),
                Tables\Columns\TextColumn::make('x_por_mina')
                    ->label(__('X por Mina')),
                Tables\Columns\TextColumn::make('x_a_cada_5')
                    ->label(__('X a cada 5')),
                Tables\Columns\TextColumn::make('bet_loss')
                    ->label(__('% Bet Loss')),
                Tables\Columns\TextColumn::make('start_cycle_at')
                    ->label(__('Ciclo Iniciado')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])->defaultPagination(1);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditMinesConfig::route('/'),
        ];
    }
}
