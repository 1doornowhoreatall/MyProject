<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Models\Category;
use App\Models\Game;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Imports para lógica condicional do formulário
use Filament\Forms\Get;
use Filament\Forms\Set;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'TODOS OS JOGOS';

    protected static ?string $modelLabel = 'Todos os Jogos';

    /**
     * @dev
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
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Select::make('provider_id')
                                    ->label(__('PROVEDOR DO JOGO'))
                                    ->placeholder(__('Selecione um provedor'))
                                    ->relationship(name: 'provider', titleAttribute: 'name')
                                    ->options(fn () => Provider::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('game_name')
                                            ->label(__('NOME DO JOGO'))
                                            ->placeholder(__('Digite o nome do jogo'))
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('views')
                                            ->label(__('VEZES JOGADO'))
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),

                                Forms\Components\Section::make('CONFIGURAÇÕES DO JOGO')
                                    ->description('O ID do jogo e o código do jogo devem ser iguais para que funcione!')
                                    ->schema([
                                        Forms\Components\TextInput::make('game_id')
                                            ->label(__('ID DO JOGO'))
                                            ->placeholder(__('Digite o ID do jogo'))
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('game_code')
                                            ->placeholder(__('Digite o código do jogo'))
                                            ->label(__('CÓDIGO DO JOGO'))
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\Select::make('categories')
                                            ->label(__('CATEGORIA DO JOGO'))
                                            ->placeholder(__('Selecione categorias para seu jogo'))
                                            ->multiple()
                                            ->relationship('categories', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->columnSpanFull(),
                                    ])->columns(3),

                                Forms\Components\Section::make('CONFIGURAÇÕES DE EXIBIÇÃO')
                                    ->description('Configurações de exibição do jogo na plataforma.')
                                    ->schema([
                                        Forms\Components\Toggle::make('show_home')->label(__('MOSTRAR NA HOME')),
                                        Forms\Components\Toggle::make('is_featured')->label(__('DESTAQUE NA HOME')),
                                        Forms\Components\Toggle::make('status')->label(__('STATUS DO JOGO'))->default(true)->required(),
                                        Forms\Components\Toggle::make('original')->label(__('Jogo original'))->default(false)->required(),
                                    ])->columns(3),

                                // === BLOCO: capa via upload OU URL externa ===
                                Forms\Components\Section::make('CAPA DO JOGO')
                                    ->schema([
                                        Forms\Components\Select::make('cover_mode')
                                            ->label(__('Como deseja definir a capa?'))
                                            ->options([
                                                'upload' => 'Enviar capa',
                                                'url'    => 'Usar URL da imagem',
                                            ])
                                            ->live()
                                            ->default('upload')
                                            ->afterStateHydrated(function (Set $set, ?Model $record) {
                                                if ($record && filled($record->cover) && Str::startsWith($record->cover, ['http://', 'https://'])) {
                                                    $set('cover_mode', 'url');
                                                    $set('cover_url', $record->cover);
                                                } else {
                                                    $set('cover_mode', 'upload');
                                                }
                                            })
                                            ->helperText('Envie um arquivo ou informe uma URL completa (ex.: https://imagensfivers.com/Games/Pragmatic/vs5joker.webp)'),

                                        Forms\Components\TextInput::make('cover_url')
                                            ->label(__('URL da imagem'))
                                            ->placeholder(__('https://exemplo.com/minha-capa.webp'))
                                            ->visible(fn (Get $get) => $get('cover_mode') === 'url')
                                            ->required(fn (Get $get) => $get('cover_mode') === 'url')
                                            ->maxLength(2048)
                                            ->url()
                                            ->rule('active_url')
                                            ->columnSpanFull()
                                            ->helperText('A imagem será carregada diretamente desta URL.'),

                                        Forms\Components\FileUpload::make('cover')
                                            ->label(__('Capa (upload)'))
                                            ->placeholder(__('Carregue a capa do jogo'))
                                            ->image()
                                            ->imageEditor()
                                            ->visible(fn (Get $get) => $get('cover_mode') === 'upload')
                                            ->required(fn (Get $get) => $get('cover_mode') === 'upload')
                                            // ->disk('public')
                                            ->columnSpanFull()
                                            ->helperText('Tamanho recomendado: 322x322.'),
                                    ]),
                            ]),
                    ])->columns(1)
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label(__('IMAGEM DO JOGO')),

                Tables\Columns\TextColumn::make('provider.name')
                    ->label(__('PROVEDOR DO JOGO'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('game_name')
                    ->label(__('NOME DO JOGO'))
                    ->searchable(),

                // 🔹 Mostrar categorias como tags (limite para não poluir)
                Tables\Columns\TagsColumn::make('categories.name')
                    ->label(__('CATEGORIAS'))
                    ->limit(3),

                Tables\Columns\ToggleColumn::make('show_home')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state == 1) {
                            $record->update(['status' => 1]);
                        }
                    })
                    ->label(__('MOSTRAR NA HOME')),

                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label(__('DESTAQUE NA HOME')),

                Tables\Columns\ToggleColumn::make('original')
                    ->label(__('Game original')),

                Tables\Columns\TextColumn::make('views')
                    ->label(__('VEZES JOGADO'))
                    ->icon('heroicon-o-eye')
                    ->numeric()
                    ->formatStateUsing(fn (Game $record): string => \Helper::formatNumber($record->views))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('Provedor')
                    ->relationship('provider', 'name')
                    ->label(__('Provedor'))
                    ->indicator('Provedor'),

                // 🔹 Filtro por categoria
                SelectFilter::make('Categoria')
                    ->relationship('categories', 'name')
                    ->label(__('Categoria'))
                    ->indicator('Categoria'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // ✅ NOVO: Definir categorias em massa
                Tables\Actions\BulkAction::make('Definir Categorias')
                    ->icon('heroicon-m-tag')
                    ->color('primary')
                    ->deselectRecordsAfterCompletion()
                    ->form([
                        Forms\Components\Select::make('categories')
                            ->label(__('Categorias'))
                            ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required()
                            ->helperText('Selecione uma ou mais categorias para aplicar aos jogos selecionados.'),

                        Forms\Components\Radio::make('strategy')
                            ->label(__('Como aplicar?'))
                            ->options([
                                'append'  => 'Adicionar às existentes',
                                'replace' => 'Substituir existentes',
                            ])
                            ->default('append')
                            ->inline()
                            ->required(),
                    ])
                    // Pré-preenche com a interseção das categorias dos selecionados
                    ->mountUsing(function (\Filament\Forms\ComponentContainer $form, $records) {
                        /** @var \Illuminate\Support\Collection $records */
                        $sets = $records->map(
                            fn (Game $g) => $g->categories()->pluck('id')->all()
                        );

                        $common = [];
                        if ($sets->isNotEmpty()) {
                            $common = array_values(array_intersect(...$sets->all()));
                        }

                        $form->fill([
                            'categories' => $common,
                            'strategy'   => 'append',
                        ]);
                    })
                    ->action(function ($records, array $data) {
                        $categoryIds = $data['categories'] ?? [];
                        $strategy    = $data['strategy'] ?? 'append';

                        foreach ($records as $record) {
                            if ($strategy === 'replace') {
                                $record->categories()->sync($categoryIds);
                            } else {
                                $record->categories()->syncWithoutDetaching($categoryIds);
                            }
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('Aplicar categorias aos jogos selecionados'))
                    ->modalSubmitActionLabel('Aplicar'),

                // (Opcional) Limpar categorias
                Tables\Actions\BulkAction::make('Limpar Categorias')
                    ->icon('heroicon-m-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->categories()->sync([]); // remove todas as categorias
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('Ativar Jogos')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        return $records->each->update(['status' => 1]);
                    }),

                Tables\Actions\BulkAction::make('Desativar Jogos')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        return $records->each(function ($record) {
                            $record->update(['status' => 0]);
                        });
                    }),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit'   => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
