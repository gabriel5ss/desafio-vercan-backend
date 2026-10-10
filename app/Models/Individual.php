<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Individual extends Model
{
    protected $table = 'individuals';

    protected $fillable = ['name', 'nickname', 'cpf', 'rg'];
}
