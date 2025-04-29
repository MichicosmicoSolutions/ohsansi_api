<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OlimpycAndCategorias extends Model
{
    use HasFactory;
    public $timestamps = false;
    public function area()
    {
        return $this->belongsTo(Areas::class);
    }

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function olympic()
    {
        return $this->belongsTo(Olympics::class);
    }
}