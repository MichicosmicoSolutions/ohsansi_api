<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalData extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "ci",
        "ci_expedition",
        "names",
        "last_names",
        "birthdate",
        "email",
        "phone_number",
    ];
    public function legalTutor()
    {
        return $this->hasOne(LegalTutors::class);
    }

    public function competitor()
    {
        return $this->hasOne(Competitors::class);
    }
}
