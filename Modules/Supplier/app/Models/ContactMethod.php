<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Supplier\Database\Factories\ContactMethodFactory;

#[UseFactory(ContactMethodFactory::class)]
class ContactMethod extends Model
{
    /** @use HasFactory<ContactMethodFactory> */
    use HasFactory;

    protected $table = 'contact_methods';

    protected $fillable = ['kind', 'value', 'label'];
}
