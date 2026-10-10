<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = ['corporate_name', 'trade_name', 'cnpj', 'state_registration_indicator', 'state_registration', 'municipal_registration', 'tax_regime', 'cnpj_status'];
}
