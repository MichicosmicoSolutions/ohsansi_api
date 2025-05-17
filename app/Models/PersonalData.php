<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PersonalData",
 *     title="Personal Data",
 *     description="Represents personal identification and contact information for an individual.",
 *     required={"ci", "ci_expedition", "names", "last_names", "birthdate", "email", "email", "phone_number", "gender"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the personal data record",
 *         readOnly=true,
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="ci",
 *         type="string",
 *         description="Cedula de Identidad (National ID number)",
 *         example="1234567"
 *     ),
 *     @OA\Property(
 *         property="ci_expedition",
 *         type="string",
 *         description="Place of expedition for the CI (e.g., LP for La Paz)",
 *         nullable=true,
 *         example="LP"
 *     ),
 *     @OA\Property(
 *         property="names",
 *         type="string",
 *         description="First name(s) of the individual",
 *         example="Juan Carlos"
 *     ),
 *     @OA\Property(
 *         property="last_names",
 *         type="string",
 *         description="Last name(s) of the individual",
 *         example="Perez Garcia"
 *     ),
 *     @OA\Property(
 *         property="birthdate",
 *         type="string",
 *         format="date",
 *         description="Date of birth",
 *         example="1995-08-15"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email address",
 *         example="juan.perez@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Contact phone number",
 *         example="71234567"
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         description="Gender of the individual (e.g., M, F)",
 *         example="M"
 *     ),
 * );
 * @OA\Schema(
 *     schema="CompetitorPersonalData",
 *     title="Personal Data",
 *     description="Represents personal identification and contact information for an individual.",
 *     required={"ci", "ci_expedition", "names", "last_names", "birthdate", "email", "email", "phone_number", "gender"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the personal data record",
 *         readOnly=true,
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="ci",
 *         type="string",
 *         description="Cedula de Identidad (National ID number)",
 *         example="1234567"
 *     ),
 *     @OA\Property(
 *         property="ci_expedition",
 *         type="string",
 *         description="Place of expedition for the CI (e.g., LP for La Paz)",
 *         nullable=true,
 *         example="LP"
 *     ),
 *     @OA\Property(
 *         property="names",
 *         type="string",
 *         description="First name(s) of the individual",
 *         example="Juan Carlos"
 *     ),
 *     @OA\Property(
 *         property="last_names",
 *         type="string",
 *         description="Last name(s) of the individual",
 *         example="Perez Garcia"
 *     ),
 *     @OA\Property(
 *         property="birthdate",
 *         type="string",
 *         format="date",
 *         description="Date of birth",
 *         example="1995-08-15"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email address",
 *         example="juan.perez@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Contact phone number",
 *         example="71234567"
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         description="Gender of the individual (e.g., M, F)",
 *         example="M"
 *     ),
 *     @OA\Property(
 *         property="inscription",
 *         description="The inscription associated with this personal data if the person is a competitor. Eager loaded when requested.",
 *         type="object",
 *         nullable=true,
 *         ref="#/components/schemas/Inscription"
 *     ),
 *     @OA\Property(
 *         property="legal_tutor",
 *         description="The legal tutor associated with this personal data (if it is a tutor). Eager loaded when requested.",
 *         type="object",
 *         nullable=true,
 *         ref="#/components/schemas/LegalTutor"
 *     ),
 *     @OA\Property(
 *         property="accountable",
 *         description="The accountable person associated with this personal data (if it is accountable). Eager loaded when requested.",
 *         type="object",
 *         nullable=true,
 *         ref="#/components/schemas/Accountable"
 *     ),
 *     @OA\Property(
 *         property="teacher",
 *         description="The teacher associated with this personal data (if it is a teacher). Eager loaded when requested.",
 *         type="object",
 *         nullable=true,
 *         ref="#/components/schemas/Teacher"
 *     ),
 *     @OA\Property(property="is_accountable", type="boolean", description="Flag indicating if the person is an accountable tutor", example=false),
 *     @OA\Property(property="is_competitor", type="boolean", description="Flag indicating if the person is a competitor", example=true),
 *     @OA\Property(property="is_tutor", type="boolean", description="Flag indicating if the person is a tutor", example=false),
 *     @OA\property(property="is_teacher", type="boolean", description="Flag indicating if the person is a teacher", example=false),
 * )
 */
class PersonalData extends Model
{
    use HasFactory;
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "ci",
        "ci_expedition",
        "names",
        "last_names",
        "birthdate",
        "email",
        "phone_number",
        "gender",
    ];

    /**
     * Get the legal tutor associated with this personal data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\LegalTutors>
     */
    public function legalTutor()
    {
        // Assuming LegalTutors model exists and uses personal_data_id as foreign key
        return $this->hasOne(LegalTutors::class);
    }

    /**
     * Get the accountable associated with this personal data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Accountables>
     */
    public function accountable()
    {
        // Assuming Accountables model exists and uses personal_data_id as foreign key
        return $this->hasOne(Accountables::class);
    }

    /**
     * Get the teacher (academic tutor) associated with this personal data.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Teachers>
     */
    public function teacher()
    {
        // Assuming Teachers model exists and uses personal_data_id as foreign key
        return $this->hasOne(Teachers::class);
    }

    /**
     * Get the inscription associated with this personal data (as a competitor).
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Inscriptions>
     */
    public function inscription()
    {
        // Explicitly defining foreign and local keys as they differ from convention
        return $this->hasOne(
            Inscriptions::class,
            'competitor_data_id', // Foreign key on Inscriptions table
            'id'                   // Local key on PersonalData table
        );
    }

    public function isAccountable()
    {
        return $this->accountable()->exists();
    }

    public function isCompetitor()
    {
        return $this->inscription()->exists();
    }

    public function isTeacher()
    {
        return $this->teacher()->exists();
    }
    public function isTutor()
    {
        return $this->legalTutor()->exists();
    }
    
}
