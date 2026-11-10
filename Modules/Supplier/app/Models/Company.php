<?php

declare(strict_types=1);

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Supplier\Database\Factories\CompanyFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[UseFactory(CompanyFactory::class)]
final class Company extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = ['corporate_name', 'trade_name', 'cnpj', 'state_registration_indicator', 'state_registration', 'municipal_registration', 'tax_regime', 'cnpj_status'];
}
