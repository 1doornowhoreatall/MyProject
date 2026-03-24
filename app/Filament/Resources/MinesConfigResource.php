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
    protected static ?string $label            = 'Mines Config';
    protected static ?string $pluralLabel      = 'Mines Config';
    protected static ?string $navigationLabel  = 'MINES CONFIGURATION';
    protected static ?string $navigationGroup  = 'Finance';

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
                    ->label(__('Collection Goal'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('percentual_distribuicao')
                    ->label(__('% Distribution'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('minas_distribuicao')
                    ->label(__('Mines (Distribution)'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('minas_arrecadacao')
                    ->label(__('Mines (Collection)'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('x_por_mina')
                    ->label(__('Multiplier per Mine'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('x_a_cada_5')
                    ->label(__('Increment every 5'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('bet_loss')
                    ->label(__('% Bet Loss'))
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\Toggle::make('modo_influenciador')
                    ->label(__('Influencer Mode'))
                    ->helperText('Se ativo, o usuário só ganha.')
                    ->default(false),

                Forms\Components\Toggle::make('modo_perdedor')
                    ->label(__('Loser Mode'))
                    ->helperText('Se ativo, o usuário só perde.')
                    ->default(false),

                // Campos apenas informativos (disabled)
                Forms\Components\TextInput::make('modo_atual')
                    ->label(__('Current Mode'))
                    ->disabled(),

                Forms\Components\TextInput::make('total_arrecadado')
                    ->label(__('Total Collected'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('total_distribuido')
                    ->label(__('Total Distributed'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\DateTimePicker::make('start_cycle_at')
                    ->label(__('Cycle Start'))
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('modo_atual')
                    ->label(__('Current Mode'))
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
                    ->label(__('Collection Goal')),
                Tables\Columns\TextColumn::make('percentual_distribuicao')
                    ->label(__('% Distribution')),
                Tables\Columns\TextColumn::make('minas_distribuicao')
                    ->label(__('Mines (Distribution)')),
                Tables\Columns\TextColumn::make('minas_arrecadacao')
                    ->label(__('Mines (Collection)')),
                Tables\Columns\TextColumn::make('x_por_mina')
                    ->label(__('X per Mine')),
                Tables\Columns\TextColumn::make('x_a_cada_5')
                    ->label(__('X every 5')),
                Tables\Columns\TextColumn::make('bet_loss')
                    ->label(__('% Bet Loss')),
                Tables\Columns\TextColumn::make('start_cycle_at')
                    ->label(__('Cycle Started')),
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
