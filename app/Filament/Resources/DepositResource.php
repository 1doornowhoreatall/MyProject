<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'DEPOSIT MANAGEMENT';
    
    protected static ?string $modelLabel = 'DEPOSIT MANAGEMENT';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $slug = 'todos-depositos';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 0)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 0)->count() > 5 ? 'success' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Deposit Registration')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Users')
                            ->placeholder('Select a user')
                            ->options(
                                fn($get) => User::query()
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->default(0.00),
                        Forms\Components\FileUpload::make('proof')
                            ->label('Proof')
                            ->placeholder('Upload proof image')
                            ->image()
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->label('Paid')
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('USER')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('PAYMENT ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('AMOUNT')
                    ->formatStateUsing(fn(Deposit $record): string => '€ ' . number_format($record->amount, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('STATUS')
                    ->formatStateUsing(fn(Deposit $record): string => $record->status ? 'Paid' : 'Unpaid')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('CREATION DATE')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('UPDATE DATE')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('data')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('status')
                    ->label('Status')
                    ->default(null) // Define "Todos" como padrão
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                '' => 'All',
                                '1' => 'Paid',
                                '0' => 'Unpaid',
                            ])
                            ->default(''), // Define "All" como padrão
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['status']) && $data['status'] !== '') {
                            $query->where('status', $data['status']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mudarParaPago')
                    ->label('Mark as Paid')
                    ->action(function (Deposit $record) {
                        $record->update(['status' => 1]);
                    })
                    ->icon('heroicon-o-check')
                    ->visible(fn(Deposit $record) => $record->status == 0), // Apenas mostrar para "Não Pago"
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(false) // Remover confirmação de exclusão
                    ->modalHeading(fn() => null) // Remover o cabeçalho do modal
                    ->modalButton(fn() => null)  // Remover o botão do modal
                    ->modalDescription(fn() => null), // Remover a descrição do modal
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(false) // Remover confirmação de exclusão em massa
                        ->modalHeading(fn() => null) // Remover o cabeçalho do modal
                        ->modalButton(fn() => null)  // Remover o botão do modal
                        ->modalDescription(fn() => null), // Remover a descrição do modal
                ]),
            ])
            ->emptyStateActions([
                // Ações do estado vazio
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
            'index' => Pages\ListDeposits::route('/'),
        ];
    }
}
