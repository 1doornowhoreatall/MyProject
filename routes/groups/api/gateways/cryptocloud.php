<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Wallet\DepositController;

Route::post('cryptocloud/webhook', [DepositController::class, 'webhookCryptoCloud']);
