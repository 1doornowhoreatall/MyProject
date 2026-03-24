<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helpers\Core;
use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdraw;
use App\Models\Setting;
use App\Models\SuitPayPayment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Order;
use App\Models\Withdrawal;
use App\Notifications\NewWithdrawalNotification;
use App\Traits\Gateways\CryptoCloudTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::whereUserId(auth('api')->id())->where('active', 1)->first();
        return response()->json(['wallet' => $wallet], 200);
    }

    public function myWallet()
    {
        $wallets = Wallet::whereUserId(auth('api')->id())->get();
        return response()->json(['wallets' => $wallets], 200);
    }

    public function withdrawalFromModal($id, Request $request)
    {
        // 1) Captura a senha enviada pela query string
        $senhaInformada = $request->query('senha');

        // 2) Verifica se a senha coincide com a do .env
        if (!$senhaInformada || $senhaInformada !== env('TOKEN_DE_2FA')) {
            // Se for diferente ou inexistente, rejeita e mostra aviso
            Notification::make()
                ->title('Incorrect password')
                ->body('The provided password is incorrect or was not provided.')
                ->danger()
                ->send();

            return back();
        }

        // *** Se a senha está OK, prosseguimos com o saque ***

        $setting = Core::getSetting();
        $tipo = $request->input("tipo");
        
        $withdrawal = Withdrawal::find($id);
        if ($tipo == "afiliado") {
            $withdrawal = AffiliateWithdraw::find($id);
        }

        if (!$withdrawal) {
            Notification::make()
                ->title('Withdrawal error')
                ->body('Withdrawal not found')
                ->danger()
                ->send();
            return back();
        }

        // Only handle manual processing - just notify admin to do it manually
        Notification::make()
            ->title('Manual Action Required')
            ->body('Please process this crypto withdrawal manually and then update the status.')
            ->warning()
            ->send();
            
        // Optionally mask it as processed if admin clicks the button confirming they did it
        // Or leave it pending until they change status elsewhere.
        // We will mark it as processed here assuming the button click means the admin sent the funds.
        $withdrawal->update(['status' => 1]);
        $resultado = true;

        if ($resultado == true) {
            Notification::make()
                ->title('Withdrawal requested')
                ->body('Withdrawal requested successfully')
                ->success()
                ->send();

            return back();
        } else {
            Notification::make()
                ->title('Withdrawal error')
                ->body('Error requesting withdrawal')
                ->danger()
                ->send();

            return back();
        }
    }

    public function setWalletActive($id)
    {
        // Primeiro, desativa a carteira ativa do usuário autenticado
        $checkWallet = Wallet::where('user_id', auth('api')->id())
            ->where('active', 1)
            ->first();
        if ($checkWallet) {
            $checkWallet->update(['active' => 0]);
        }

        // Busca a carteira garantindo que ela pertença ao usuário autenticado
        $wallet = Wallet::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$wallet) {
            return response()->json([
                'error' => 'Wallet not found or unauthorized access'
            ], 403);
        }

        $wallet->update(['active' => 1]);
        return response()->json(['wallet' => $wallet], 200);
    }

    public function requestWithdrawal(Request $request)
    {
        $setting = Setting::first();

        if (auth('api')->check()) {

            $userId = auth('api')->id();
            // Nome SEM depender de auth()->user():
            $userName = User::where('id', $userId)->value('name');

            // Verificar a última aposta do usuário
            $lastOrder = Order::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastOrder) {
                $lastBetTime = $lastOrder->created_at;
                $currentTime = now();

                // Verificar se já passaram 1 minuto desde a última aposta
                if ($currentTime->diffInMinutes($lastBetTime) < 1) {
                    return response()->json([
                        'error' => 'You can only withdraw after 1 minute from your last bet.'
                    ], 400);
                }
            }

            // Regras de validação
            $rules = [];
            if ($request->type === 'crypto') {
                $rules = [
                    'amount'   => ['required', 'numeric', 'min:' . $setting->min_withdrawal, 'max:' . $setting->max_withdrawal],
                    'wallet_address' => 'required',
                ];
            } else {
                // Fallback to generic validation if not crypto
                $rules = [
                    'amount'   => ['required', 'numeric', 'min:' . $setting->min_withdrawal, 'max:' . $setting->max_withdrawal],
                ];
            }

            if ($request->type === 'bank') {
                $rules = [
                    'amount' => ['required', 'numeric', 'min:' . $setting->min_withdrawal, 'max:' . $setting->max_withdrawal],
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            /// verificar o limite de saque
            if (!empty($setting->withdrawal_limit)) {
                switch ($setting->withdrawal_period) {
                    case 'daily':
                        $registrosDiarios = Withdrawal::whereDate('created_at', now()->toDateString())->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the daily withdrawal limit')], 400);
                        }
                        break;

                    case 'weekly':
                        $registrosDiarios = Withdrawal::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the weekly withdrawal limit')], 400);
                        }
                        break;

                    case 'monthly':
                        // CORRIGIDO: era whereMonth('data', ...) -> 'created_at'
                        $registrosDiarios = Withdrawal::whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month)
                            ->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the monthly withdrawal limit')], 400);
                        }
                        break;

                    case 'yearly':
                        $registrosDiarios = Withdrawal::whereYear('created_at', now()->year)->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the yearly withdrawal limit')], 400);
                        }
                        break;
                }
            }

            if ($request->amount > $setting->max_withdrawal) {
                return response()->json(['error' => 'You have exceeded the maximum withdrawal limit of: ' . $setting->max_withdrawal], 400);
            }

            // Saldo disponível para saque (sem depender de auth()->user())
            $balanceWithdrawal = (float) Wallet::where('user_id', $userId)->value('balance_withdrawal');

            if ((float) $request->amount > $balanceWithdrawal) {
                return response()->json(['error' => 'You do not have enough balance'], 400);
            }

            // Montagem do payload de criação do saque
            $data = [
                'user_id'  => $userId,
                'amount'   => \Helper::amountPrepare($request->amount),
                'type'     => $request->type,
                'currency' => $request->currency,
                'symbol'   => $request->symbol,
                'status'   => 0,
                'cpf'      => $request->cpf ?? '',
                // Nome vindo direto do banco pelo user_id:
                'name'     => $userName, // sem fallback esquisito
            ];

            if ($request->type === 'crypto') {
                $data['pix_key']  = $request->wallet_address;
                $data['pix_type'] = 'crypto';
            }

            $withdrawal = Withdrawal::create($data);

            if ($withdrawal) {
                // Decrementa o saldo de saque com segurança
                Wallet::where('user_id', $userId)->decrement('balance_withdrawal', (float) $request->amount);

                // Notifica admins usando o nome buscado via user_id
                $admins = User::where('role_id', 0)->get();
                foreach ($admins as $admin) {
                    $admin->notify(new NewWithdrawalNotification($userName, $request->amount));
                }

                return response()->json([
                    'status'  => true,
                    'message' => 'Withdrawal completed successfully',
                ], 200);
            }

            return response()->json(['error' => 'Error processing withdrawal'], 400);
        }

        return response()->json(['error' => 'Erro ao realizar o saque'], 400);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
