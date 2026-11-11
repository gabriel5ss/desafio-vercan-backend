<?php

declare(strict_types=1);

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Supplier\Database\Factories\ContactMethodFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[UseFactory(ContactMethodFactory::class)]
final class ContactMethod extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ContactMethodFactory> */
    use HasFactory;

    protected $table = 'contact_methods';

    protected $fillable = ['kind', 'value', 'label'];
}
