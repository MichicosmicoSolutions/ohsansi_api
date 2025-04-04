<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicTutors extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_data_id',
    ];

    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id', 'id');
    }
}
