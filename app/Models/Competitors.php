<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competitors extends Model
{
    use HasFactory;

    protected $fillable = [
        'course',
        'school_id',
        'legal_tutor_id',
        'personal_data_id',
    ];
    public function school()
    {
        return $this->belongsTo(Schools::class, 'school_id');
    }
    public function legalTutor()
    {
        return $this->belongsTo(LegalTutors::class, 'legal_tutor_id');
    }
    public function academicTutor()
    {
        return $this->belongsTo(AcademicTutors::class, 'academic_tutor_id');
    }
    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id');
    }
    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class, 'competitor_id');
    }
}

