<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMethod extends Model
{
    protected $table = 'contact_methods';

    protected $fillable = ['kind', 'value', 'label'];
}
