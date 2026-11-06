<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Individual extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'individuals';

    protected $fillable = ['name', 'nickname', 'cpf', 'rg'];
}
