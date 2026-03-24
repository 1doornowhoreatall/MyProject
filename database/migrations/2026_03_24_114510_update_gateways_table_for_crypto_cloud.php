<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            // Drop old fiat columns
            $table->dropColumn([
                'suitpay_uri', 'suitpay_cliente_id', 'suitpay_cliente_secret',
                'stripe_production', 'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_key',
                'ezze_uri', 'ezze_client', 'ezze_secret', 'ezze_user', 'ezze_senha',
                'digito_uri', 'digito_client', 'digito_secret',
                'ondapay_uri', 'ondapay_client', 'ondapay_secret',
                'bspay_uri', 'bspay_cliente_id', 'bspay_cliente_secret'
            ]);

            // Add new crypto column
            $table->string('cryptocloud_shop_id')->nullable();
            $table->string('cryptocloud_api_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->dropColumn(['cryptocloud_shop_id', 'cryptocloud_api_key']);
        });
    }
};
