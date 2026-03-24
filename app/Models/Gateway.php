<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Gateway extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gateways';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cryptocloud_shop_id',
        'cryptocloud_api_key',
    ];

    protected $hidden = array('updated_at');

    /**
     * Get the API Key encrypted/hidden for demo.
     */
    protected function cryptocloudApiKey(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }
}
