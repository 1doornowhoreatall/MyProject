<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'GESTÃO DE CARTEIRAS';

    protected static ?string $modelLabel = 'Carteiras';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $slug = 'minha-carteira';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('balance')
                            ->label(__('SALDO DE DEPÓSITO'))
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('balance_bonus')
                            ->label(__('SALDO DE BÔNUS'))
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('refer_rewards')
                            ->label(__('SALDO DE AFILIADO'))
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('balance_withdrawal')
                            ->label(__('SALDO LIBERADO PARA SAQUE'))
                            ->required()
                            ->numeric()
                            ->default(0.00),
                    ])->columns(5),
                // Nova seção para confirmação de alteração com senha de 2FA
                Forms\Components\Section::make('Confirmação de Alteração')
                    ->schema([
                        Forms\Components\TextInput::make('admin_password')
                            ->label(__('Senha de 2FA'))
                            ->placeholder(__('Digite a senha de 2FA'))
                            ->password()
                            ->required()
                            // Usa a regra "in:" para validar o valor sem precisar de closure customizada
                            ->rules(['in:' . env('TOKEN_DE_2FA')])
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('USUÁRIO'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label(__('SALDO DE DEPÓSITO'))
                    ->formatStateUsing(fn(string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_withdrawal')
                    ->label(__('SALDO DE SAQUE'))
                    ->formatStateUsing(fn(string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_bonus')
                    ->label(__('SALDO DE BÔNUS'))
                    ->formatStateUsing(fn(string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('CADASTRADO EM'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label(__('Criado a partir de')),
                        DatePicker::make('created_until')->label(__('Criado até')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Criado a partir de ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Criado até ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
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
                //Tables\Actions\CreateAction::make(),
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
            'index'  => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit'   => Pages\EditWallet::route('/{record}/edit'),
        ];
    }

    /**
     * Método customizado para salvar alterações.
     *
     * Este método deve ser chamado nas Pages de edição, e valida a senha de 2FA antes de atualizar.
     *
     * @return void
     */
    public function submit(): void
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Atenção'))
                    ->body('Você não pode realizar esta alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }

            // Validação da senha de 2FA
            if (
                !isset($this->data['admin_password']) ||
                $this->data['admin_password'] !== env('TOKEN_DE_2FA')
            ) {
                Notification::make()
                    ->title(__('Acesso Negado'))
                    ->body('A senha de 2FA está incorreta. Você não pode atualizar os dados.')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Wallet::find($this->record->id);
            if (!empty($setting)) {
                if ($setting->update($this->data)) {
                    Notification::make()
                        ->title(__('Sucesso'))
                        ->body('Os dados foram atualizados com sucesso!')
                        ->success()
                        ->send();
                }
            }
        } catch (\Filament\Support\Exceptions\Halt $exception) {
            Notification::make()
                ->title(__('Erro ao alterar dados!'))
                ->body('Erro ao alterar dados!')
                ->danger()
                ->send();
        }
    }
}
