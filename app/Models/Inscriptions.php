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

    public function competitor()
    {
        return $this->belongsTo(Competitors::class, 'competitor_id');
    }
    public function olympic()
    {
        return $this->belongsTo(Olympics::class, 'olympic_id');
    }
    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}
