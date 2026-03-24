<?php

namespace App\Filament\Pages;

use App\Models\ConfigRoundsFree;
use App\Models\Game;
use App\Models\GamesKey;
use App\Models\User;
use App\Services\PlayFiverService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule; // 👈 importa o Rule

class RoundsFreePage extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $view  = 'filament.pages.rounds-free-page';
    protected static ?string $title = 'RODADAS GRÁTIS';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public ?array $data = [];
    public ?GamesKey $setting;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $users = User::orderBy('email')->pluck('email', 'email');

        // ✅ só jogos com free spins e ativos
        $games = Game::query()
            ->where('status', 1)
            ->where('has_freespins', 1)
            ->orderBy('game_name')
            ->pluck('game_name', 'game_code');

        return $form
            ->schema([
                Section::make('Rodadas Grátis')
                    ->schema([
                        Select::make('email')
                            ->label(__('Player'))
                            ->options($users)
                            ->searchable()
                            ->required()
                            ->rules(['required','email']),

                        Select::make('game_code')
                            ->label(__('Jogo'))
                            ->options($games)
                            ->searchable()
                            ->required()
                            // ✅ garante no back que o game_code existe e tem has_freespins=1+status=1
                            ->rules([
                                'required',
                                Rule::exists('games', 'game_code')
                                    ->where(fn ($q) => $q->where('status', 1)->where('has_freespins', 1)),
                            ]),

                        TextInput::make('rounds')
                            ->label(__('Quantidade de rodadas'))
                            ->numeric()
                            // ✅ trava entre 1 e 30
                            ->rules(['required','integer','between:1,30'])
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Confirmação de Alteração')
                    ->schema([
                        TextInput::make('admin_password')
                            ->label(__('Senha de 2FA'))
                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => null),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getTableQuery(): Builder
    {
        // (opcional) só mostra configs cujos jogos ainda têm free spins
        return ConfigRoundsFree::query()
            ->with('game')
            ->whereHas('game', fn ($q) => $q->where('has_freespins', 1));
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('game.game_name')->label(__('Nome do Jogo'))->sortable(),
            TextColumn::make('game_code')->label(__('Código do Jogo'))->sortable(),
            TextColumn::make('spins')->label(__('Rodadas'))->sortable(),
            TextColumn::make('value')->label(__('Valor'))->money('BRL', true)->sortable(),
            TextColumn::make('created_at')->label(__('Criado em'))->dateTime('d/m/Y H:i')->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('Apagar'))
                ->requiresConfirmation()
                ->modalHeading(__('Confirmar Exclusão'))
                ->modalSubheading('Tem certeza que deseja apagar esta configuração?'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Criar configuração'))
                ->modalHeading(__('Nova Configuração de Rodadas Grátis'))
                ->modalWidth('lg')
                ->disableCreateAnother()
                ->using(function (array $data) {
                    return ConfigRoundsFree::create($data);
                })
                ->form([
                    // ✅ opções filtradas + regra exists com where()
                    Select::make('game_code')
                        ->label(__('Jogo'))
                        ->options(
                            Game::where('status', 1)
                                ->where('has_freespins', 1)
                                ->orderBy('game_name')
                                ->pluck('game_name', 'game_code')
                        )
                        ->searchable()
                        ->required()
                        ->rules([
                            'required',
                            Rule::exists('games', 'game_code')
                                ->where(fn ($q) => $q->where('status', 1)->where('has_freespins', 1)),
                        ]),

                    TextInput::make('spins')
                        ->label(__('Quantidade de Rodadas'))
                        ->numeric()
                        ->rules(['required','integer','between:1,30']) // ✅ máx. 30
                        ->required(),

                    TextInput::make('value')
                        ->label(__('Valor (R$)'))
                        ->numeric()
                        ->rules(['required','numeric','min:0'])
                        ->required(),
                ]),
        ];
    }

    public function submit(): void
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title(__('Atenção'))
                    ->body('Não é possível alterar em modo demo.')
                    ->danger()
                    ->send();
                return;
            }

            if (
                !isset($this->data['admin_password']) ||
                $this->data['admin_password'] !== env('TOKEN_DE_2FA')
            ) {
                Notification::make()
                    ->title(__('Acesso Negado'))
                    ->body('Senha de 2FA incorreta.')
                    ->danger()
                    ->send();
                return;
            }

            // ✅ valida de novo no submit (defesa extra)
            $this->validate([
                'data.email'     => ['required','email'],
                'data.game_code' => [
                    'required',
                    Rule::exists('games', 'game_code')->where(fn ($q) => $q->where('status', 1)->where('has_freespins', 1)),
                ],
                'data.rounds'    => ['required','integer','between:1,30'],
            ]);

            $dados = [
                'username'  => $this->data['email'],
                'game_code' => $this->data['game_code'],
                'rounds'    => (int) $this->data['rounds'],
            ];

            $result = PlayFiverService::RoundsFree($dados);

            if ($result['status']) {
                Notification::make()
                    ->title(__('Rodadas grátis'))
                    ->body('Agendamento realizado com sucesso.')
                    ->success()
                    ->send();

                $this->data = [];
                $this->form->fill();
            } else {
                Notification::make()
                    ->title(__('Rodadas grátis'))
                    ->body($result['message'] ?? 'Falha ao agendar.')
                    ->danger()
                    ->send();
            }
        } catch (Halt $e) {
            Notification::make()
                ->title(__('Erro'))
                ->body('Não foi possível completar a operação.')
                ->danger()
                ->send();
        }
    }
}
