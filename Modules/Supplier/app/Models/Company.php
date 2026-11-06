<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Company extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'companies';

    protected $fillable = ['corporate_name', 'trade_name', 'cnpj', 'state_registration_indicator', 'state_registration', 'municipal_registration', 'tax_regime', 'cnpj_status'];
}
