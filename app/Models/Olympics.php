<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olympics extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'Presentation',
        'Requirements',
        'start_date',
        'end_date',
        'Contactos',
        'awards',
    ];
}
