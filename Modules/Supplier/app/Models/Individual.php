<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Supplier\Database\Factories\IndividualFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[UseFactory(IndividualFactory::class)]
class Individual extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<IndividualFactory> */
    use HasFactory;

    protected $table = 'individuals';

    protected $fillable = ['name', 'nickname', 'cpf', 'rg'];
}
