<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscriptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'competitor_id',
        'olympic_id',
        'area_id',
        'category_id',
        'status',
    ];
}
