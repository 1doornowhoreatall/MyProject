<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VipResource\Pages;
use App\Models\Vip;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;

class VipResource extends Resource
{
    protected static ?string $model = Vip::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'VIP LEVELS';

    protected static ?string $pluralLabel = 'VIPs';

    protected static ?string $navigationGroup = 'Promotions';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('VIP Title'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3),
                TextInput::make('required_missions')
                    ->label(__('Required Missions'))
                    ->numeric()
                    ->required(),
                TextInput::make('weekly_reward')
                    ->label(__('Weekly Reward'))
                    ->numeric()
                    ->required(),
                FileUpload::make('image')
                    ->label(__('VIP Image'))
                    ->directory('uploads/vips')
                    ->image()
                    ->imagePreviewHeight('100')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50),
                TextColumn::make('required_missions')
                    ->label(__('Required Missions'))
                    ->sortable(),
                TextColumn::make('weekly_reward')
                    ->label(__('Weekly Reward'))
                    ->sortable(),
                ImageColumn::make('image')
                    ->label(__('Image'))
                    ->size(50),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVips::route('/'),
            'create' => Pages\CreateVip::route('/create'),
            'edit' => Pages\EditVip::route('/{record}/edit'),
        ];
    }
}
