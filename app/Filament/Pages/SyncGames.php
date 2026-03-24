<?php

namespace App\Filament\Pages;

use App\Models\Game;
use App\Models\Provider;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SyncGames extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-refresh';
    protected static ?string $navigationLabel = 'Game Management';
    protected static ?string $navigationGroup = 'Games and Providers';
    protected static ?string $title = 'Game Management';
    protected static string $view = 'filament.pages.sync-games';

    public function downloadAndExtractZip()
    {
        $this->sendNotification('info', 'Starting ZIP file download...');

        // URL do arquivo ZIP
        $zipUrl = 'https://imagensfivers.com/downloads/webp_playfiver.zip';
        $zipPath = storage_path('app/temp/webp_playfiver.zip'); // Diretório temporário
        $extractPath = public_path('storage/'); // Diretório de destino

        try {
            // Criação do diretório temporário, se não existir
            if (!File::exists(storage_path('app/temp'))) {
                File::makeDirectory(storage_path('app/temp'), 0775, true);
            }

            // Baixar o arquivo ZIP
            $response = Http::withOptions(['timeout' => 120])->get($zipUrl);

            if (!$response->successful()) {
                $this->sendNotification('danger', 'Error downloading the ZIP file. Check the link.');
                return;
            }

            File::put($zipPath, $response->body());
            $this->sendNotification('success', 'ZIP file downloaded successfully.');

            // Extrair o ZIP
            $zip = new ZipArchive;

            if ($zip->open($zipPath) === true) {
                if (!File::exists($extractPath)) {
                    File::makeDirectory($extractPath, 0775, true);
                }
                $zip->extractTo($extractPath);
                $zip->close();
                $this->sendNotification('success', 'Images extracted successfully.');
            } else {
                $this->sendNotification('danger', 'Failed to open the ZIP file for extraction.');
            }

            // Limpar o arquivo ZIP
            File::delete($zipPath);
        } catch (\Exception $e) {
            $this->sendNotification('danger', 'Error during download or extraction: ' . $e->getMessage());
        }
    }
    // Botão: Sincronizar Jogos e Provedores
    public function syncGamesAndProviders()
    {
        $this->sendNotification('info', 'Starting synchronization...');
        $this->syncProviders();
        $this->syncGames();
        $this->sendNotification('success', 'Synchronization completed successfully.');
    }

    // Botão: Sincronizar Provedores
    public function syncProvidersOnly()
    {
        $this->sendNotification('info', 'Synchronizing providers...');
        $this->syncProviders();
        $this->sendNotification('success', 'Providers synchronized successfully.');
    }

    // Botão: Sincronizar Jogos
    public function syncGamesOnly()
    {
        $this->sendNotification('info', 'Synchronizing games...');
        $this->syncGames();
        $this->sendNotification('success', 'Games synchronized successfully.');
    }

    // Botão: Excluir Todos os Jogos e Provedores
    public function deleteAllData()
    {
        Provider::truncate();
        Game::truncate();
        $this->sendNotification('success', 'All games and providers have been deleted.');
    }

    private function syncProviders()
    {
        $providersResponse = Http::get('https://list.playfivers.com/providers/');
        $providersData = $providersResponse->json();

        if ($providersData['status'] !== 1) {
            $this->sendNotification('danger', 'Error fetching providers.');
            return;
        }

        foreach ($providersData['providers'] as $provider) {
            $name = $provider['provider_name'];
            $code = strtoupper($name);
            // Ajusta o caminho do cover removendo a parte inicial
            $coverPath = str_replace('https://imagensfivers.com/', '', $provider['img_url']);

            Provider::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'cover' => $coverPath,
                    'status' => 1,
                    'rtp' => 0,
                    'views' => 0,
                    'distribution' => 'play_fiver',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }

    private function syncGames()
    {
        $gamesResponse = Http::get('https://list.playfivers.com/games/');
        $gamesData = $gamesResponse->json();
    
        if ($gamesData['status'] !== 1) {
            $this->sendNotification('danger', 'Error fetching games.');
            return;
        }
    
        $providers = Provider::pluck('id', 'code');
        $existingGames = Game::pluck('game_code')->toArray();
    
        foreach ($gamesData['games'] as $game) {
            $providerCode = strtoupper($game['provider']);
            $providerId = $providers[$providerCode] ?? null;
    
            if (!$providerId) {
                continue; // se não encontrar provider, pula o jogo
            }
    
            // Ajusta o caminho do cover removendo a parte inicial
            $coverPath = str_replace('https://imagensfivers.com/', '', $game['img_url']);
    
            Game::updateOrCreate(
                ['game_code' => $game['game_code']],
                [
                    'provider_id' => $providerId,
                    'game_id'     => $game['game_code'],
                    'game_name'   => $game['game_name'],
                    'game_code'   => $game['game_code'],
                    'game_type'   => $game['game_type'],
                    'cover'       => $coverPath,
                    'status'      => $game['status'],
                    'technology'  => 'html5',
    
                    // Aqui está a inclusão do novo campo:
                    // Se preferir garantir que seja sempre inteiro, use (int) $game['original']
                    'original'    => $game['original'],
    
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]
            );
        }
    }
    

    protected function sendNotification(string $type, string $message)
    {
        Notification::make()
            ->title($message)
            ->success($type === 'success')
            ->danger($type === 'danger')
            ->info($type === 'info')
            ->send();
    }
}
