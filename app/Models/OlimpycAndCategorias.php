<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlimpycAndCategoria extends Model
{
    use HasFactory;

    protected $table = 'olimpyc_and_categorias';

    protected $fillable = [
        'olympic_id',
        'area_id',
        'category_id',
    ];
}
