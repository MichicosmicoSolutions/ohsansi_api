<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Areas;

class Categories extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $casts = [
        'range_course' => 'array',
    ];
    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
    }
}
