<?php

namespace App\Filament\Pages;

use App\Models\LogsRoundsFree;
use Filament\Pages\Page;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LogsRoundsFreePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.logs-rounds-free-page';


    protected static ?string $title = 'FREE BETS HISTORY';

    protected static ?string $slug = 'logs-rounds-free';
    public function table(Table $table): Table
    {
        return $table
            ->query(LogsRoundsFree::query()) 
            ->columns([
                TextColumn::make('username')->label(__('User'))->searchable(),
                TextColumn::make('game_code')->label(__('Game'))->searchable(),
                CheckboxColumn::make('status')->label(__("Status"))->disabled(),
                TextColumn::make('message')->label(__('Message')),
                TextColumn::make('created_at')->label(__('Date'))->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
