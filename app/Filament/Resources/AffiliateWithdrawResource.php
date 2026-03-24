<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateWithdrawResource\Pages;
use App\Models\AffiliateWithdraw;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
class AffiliateWithdrawResource extends Resource
{
    protected static ?string $model = AffiliateWithdraw::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin'); // Apenas o admin tem acesso
    }
    
    public static function getNavigationLabel(): string
    {
        return 'AFFILIATE WITHDRAWALS';
    }
    
    public static function getModelLabel(): string
    {
        return 'AFFILIATE WITHDRAWALS';
    }
    

    public static function getGloballySearchableAttributes(): array
    {
        return ['pix_type', 'bank_info', 'user.name', 'user.email'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 0)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 0)->count() > 5 ? 'success' : 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->query(AffiliateWithdraw::query())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->formatStateUsing(fn (AffiliateWithdraw $record): string => $record->symbol . ' ' . $record->amount)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pix_type')
                    ->label(__('Crypto Type'))
                    ->formatStateUsing(fn (string $state): string => \Helper::formatPixType($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pix_key')
                    ->label(__('Wallet Address')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (AffiliateWithdraw $record): string => match($record->status) {
                        0 => 'Pending',
                        1 => 'Approved',
                        2 => 'Canceled',
                        default => 'Unknown'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label(__('Creation Date'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('From')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('Until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($query) => $query->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($query) => $query->whereDate('created_at', '<=', $data['created_until']));
                    }),
                Filter::make('status')
                    ->label(__('Status'))
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                0 => 'Pending',
                                1 => 'Approved',
                                2 => 'Canceled',
                            ])
                            ->placeholder(__('Select a status')),
                    ])
                    ->query(fn ($query, $data) => isset($data['status']) ? $query->where('status', $data['status']) : $query),
            ])
            ->actions([

                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (AffiliateWithdraw $withdrawal): bool => in_array($withdrawal->status, [0, 1, 2]))
                    ->action(function (AffiliateWithdraw $withdrawal) {
                        $withdrawal->delete();
                        \Filament\Notifications\Notification::make()
                            ->title(__('Withdrawal Deleted'))
                            ->success()
                            ->persistent()
                            ->body('The withdrawal was deleted successfully.')
                            ->send();
                    }),
                    Action::make('approve_payment')
                    ->label(__('Make payment'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(AffiliateWithdraw $record): bool => !$record->status)
                
                    // Cria um form com um campo de senha
                    ->form([
                        Forms\Components\TextInput::make('senha')
                            ->label(__('Enter the password to complete the withdrawal'))
                            ->password()
                            ->required(),
                    ])
                
                    // Exibe modal
                    ->requiresConfirmation()
                
                    ->modalHeading(__('Withdrawal Confirmation'))
                    ->modalButton(__('Request Withdrawal'))
                
                    // Callback ao submeter o form do modal:
                    ->action(function (AffiliateWithdraw $record, array $data) {
                        // Verifica se preencheu a senha
                        if (! $data['senha']) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Enter the password'))
                                ->danger()
                                ->body('You did not enter the password.')
                                ->send();
                
                            return;
                        }
                
                        // Monta a rota do Controller que valida e faz o saque.
                        // Agora passamos 'tipo=afiliado' para o Controller saber que é AffiliateWithdraw
                        $route = route('withdrawal', [
                            'id' => $record->id,
                            'tipo' => 'afiliado',
                        ]);
                
                        // Redireciona com a senha por GET
                        return redirect()->to($route . '&senha=' . urlencode($data['senha']));
                        // Se preferir, use ?senha= se não houver mais parâmetros.
                        // Se a rota já tiver ?id=..., troque por '&senha=...'
                    }),
                

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([]);
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
            'index' => Pages\ListAffiliateWithdraws::route('/'),
        ];
    }
}
