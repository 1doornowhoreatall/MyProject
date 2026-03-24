<x-filament::page>
    <div class="flex flex-col items-center justify-center space-y-6">
        <!-- Title -->
        <h1 class="text-xl font-bold text-gray-700">
            {{ __('Game and Provider Management') }}
        </h1>

        <!-- Botões de Controle -->
        <div class="space-y-4">
            <!-- Button to Synchronize Games and Providers -->
            <button
                wire:click="syncGamesAndProviders"
                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Import Games and Providers') }}
            </button>

            <!-- Button to Synchronize Providers Only -->
            <button
                wire:click="syncProvidersOnly"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Import Providers') }}
            </button>

            <!-- Button to Synchronize Games Only -->
            <button
                wire:click="syncGamesOnly"
                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Import Games') }}
            </button>

            <!-- Button to Download Images -->
            <a
                href="https://imagensfivers.com/Dowload/Webp_Playfiver.zip"
                target="_blank"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Download Images') }}
            </a>

            <!-- Button to Delete All Games and Providers -->
            <button
                wire:click="deleteAllData"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Delete All Games and Providers') }}
            </button>




            <!-- Download and Extract Images -->
            <button
                wire:click="downloadAndExtractZip"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow"
            >
                {{ __('Download and Extract Images') }}
            </button>
        </div>
    </div>
</x-filament::page>
