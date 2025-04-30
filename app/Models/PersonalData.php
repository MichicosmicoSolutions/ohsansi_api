<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PersonalData",
 *     title="Personal Data Model",
 *     description="A model representing personal data of an individual.",
 *     required={"ci", "names", "last_names", "birthdate", "email", "phone_number"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the personal data record."),
 *     @OA\Property(property="ci", type="string", maxLength=10, description="The identity card number of the individual."),
 *     @OA\Property(property="ci_expedition", type="string", format="date", description="The date when the identity card was issued."),
 *     @OA\Property(property="names", type="string", maxLength=255, description="The first names of the individual."),
 *     @OA\Property(property="last_names", type="string", maxLength=255, description="The last names of the individual."),
 *     @OA\Property(property="birthdate", type="string", format="date", description="The birthdate of the individual."),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, description="The email address of the individual."),
 *     @OA\Property(property="phone_number", type="string", pattern="[0-9]{10}", description="The phone number of the individual.")
 * )
 */
class PersonalData extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "ci",
        "ci_expedition",
        "names",
        "last_names",
        "birthdate",
        "email",
        "phone_number",
    ];

    /**
     * Get the legal tutor associated with this personal data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function legalTutor()
    {
        return $this->hasOne(LegalTutors::class);
    }

    /**
     * Get the competitor associated with this personal data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function competitor()
    {
        return $this->hasOne(Competitors::class);
    }

    /**
     * Get the responsable associated with this personal data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function responsable()
    {
        return $this->hasOne(Responsables::class);
    }

    /**
     * Get the academic tutor associated with this personal data.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function academic_tutor()
    {
        return $this->hasOne(AcademicTutors::class);
    }
}
