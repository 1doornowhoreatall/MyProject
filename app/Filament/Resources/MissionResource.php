<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionResource\Pages;
use App\Models\Mission;
use Filament\Forms;
use Filament\Forms\Components\{FileUpload, Select, TextInput, Textarea};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\{ImageColumn, TextColumn};
use Filament\Tables\Table;

class MissionResource extends Resource
{
    protected static ?string $model = Mission::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $label = 'Mission';
    protected static ?string $navigationLabel = 'MANAGE MISSION';
    protected static ?string $pluralLabel = 'Missions';
    protected static ?string $navigationGroup = 'Marketing';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Description')
                ->rows(5)
                ->nullable(),

            Select::make('type')
                ->label('Mission Type')
                ->options([
                    'deposit' => 'Deposit Mission',
                    'game_bet' => 'Game Bet Mission',
                    'total_bet' => 'Total Bet Mission',
                    'rounds_played' => 'Rounds Played Mission',
                    'win_amount' => 'Win Amount Mission',
                    'loss_amount' => 'Loss Amount Mission',
                ])
                ->required()
                ->reactive() // Atualiza dinamicamente ao selecionar
                ->afterStateUpdated(function (callable $set, $state) {
                    // Reseta o campo game_id se o tipo não for 'game_bet'
                    if ($state !== 'game_bet') {
                        $set('game_id', null);
                    }
                }),

            Select::make('game_id')
                ->label('Select Game')
                ->options(
                    \DB::table('games')->pluck('game_name', 'game_id')->toArray() // Pluck com game_id como chave e game_name como valor
                )
                ->searchable()
                ->nullable()
                ->visible(fn ($get) => in_array($get('type'), ['game_bet', 'rounds_played', 'win_amount', 'loss_amount'])), // Mostra para tipos que envolvem jogos

            TextInput::make('target_amount')
                ->label('Target Value (€ or Rounds)')
                ->numeric()
                ->helperText(fn ($get) => match ($get('type')) {
                    'rounds_played' => 'Number of rounds the user needs to play.',
                    'win_amount' => 'Win amount the user needs to achieve.',
                    'loss_amount' => 'Loss amount the user needs to achieve.',
                    default => 'Amount the user needs to achieve to complete the mission.',
                })
                ->required(),

            TextInput::make('reward')
                ->label('Reward (€)')
                ->numeric()
                ->helperText('Amount the user will receive upon completing the mission.')
                ->required(),

            FileUpload::make('image')
                ->label('Mission Image')
                ->image()
                ->directory('/uploads/missoes') // Diretório onde as imagens serão salvas
                ->placeholder('Upload an image'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')
                ->label('Image')
                ->circular(),
            TextColumn::make('title')
                ->label('Title')
                ->searchable()
                ->sortable(),
            TextColumn::make('type')
                ->label('Mission Type')
                ->formatStateUsing(fn ($state) => [
                    'deposit' => 'Deposit Mission',
                    'game_bet' => 'Game Bet Mission',
                    'total_bet' => 'Total Bet Mission',
                    'rounds_played' => 'Rounds Played Mission',
                    'win_amount' => 'Win Amount Mission',
                    'loss_amount' => 'Loss Amount Mission',
                ][$state] ?? $state),
            TextColumn::make('target_amount')
                ->label('Target Value')
                ->formatStateUsing(fn ($state, $record) => match ($record->type) {
                    'rounds_played' => "{$state} Rounds",
                    default => "€ {$state}",
                }),
            TextColumn::make('reward')
                ->label('Reward')
                ->money('EUR'),
            TextColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ][$state] ?? $state),
        ])->filters([])
          ->actions([
              Tables\Actions\EditAction::make(),
          ])->bulkActions([
              Tables\Actions\DeleteBulkAction::make(),
          ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'edit' => Pages\EditMission::route('/{record}/edit'),
        ];
    }
}
