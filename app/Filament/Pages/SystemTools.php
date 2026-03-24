<?php

namespace App\Filament\Pages;

use App\Services\CacheNuker;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemTools extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'SYSTEM';
    protected static ?string $title           = 'System Tools';
    protected static ?string $navigationLabel = 'CACHE CLEAR';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.system-tools';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_app_cache')
                ->label(__('Clear application cache'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (CacheNuker $nuker) {
                    $nuker->run(['deep' => true, 'sessions' => false, 'queues' => false]);
                    Notification::make()->title(__('Cache cleared successfully'))->success()->send();
                }),

            Actions\Action::make('clear_sessions')
                ->label(__('Clear sessions'))
                ->icon('heroicon-o-user-minus')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (CacheNuker $nuker) {
                    $nuker->run(['deep' => false, 'sessions' => true, 'queues' => false]);
                    Notification::make()->title(__('Sessions cleared'))->success()->send();
                }),

            Actions\Action::make('clear_advanced')
                ->label(__('Advanced clear (event/opcache)'))
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->requiresConfirmation()
                ->action(function (CacheNuker $nuker) {
                    // “avançado”: sem sessões/filas; optimize+event+opcache já rodam no service
                    $nuker->run(['deep' => false, 'sessions' => false, 'queues' => false]);
                    Notification::make()->title(__('Advanced clear completed'))->success()->send();
                }),

            Actions\Action::make('rebuild_caches')
                ->label(__('Rebuild caches (config/routes/views)'))
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    try { \Illuminate\Support\Facades\Artisan::call('config:cache'); } catch (\Throwable $e) {}
                    try { \Illuminate\Support\Facades\Artisan::call('route:cache'); } catch (\Throwable $e) {}
                    try { \Illuminate\Support\Facades\Artisan::call('view:cache'); } catch (\Throwable $e) {}
                    try { \Illuminate\Support\Facades\Artisan::call('event:cache'); } catch (\Throwable $e) {}
                    try { \Illuminate\Support\Facades\Artisan::call('optimize'); } catch (\Throwable $e) {}
                    Notification::make()->title(__('Caches rebuilt'))->success()->send();
                }),
        ];
    }
}
