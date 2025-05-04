<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Accountable",
 *     type="object",
 *     title="Accountable Model",
 *     description="Modelo Accountables que representa a quien se responsabiliza por el pago de las inscripciones",
 *     required={"personal_data_id"},
 *     @OA\Property(
 *         property="personal_data_id",
 *         type="integer",
 *         format="int64",
 *         description="ID de los datos personales asociados (actúa como clave primaria y foránea).",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="personal_data",
 *         type="object",
 *         description="The associated PersonalData object (loaded via relationship)",
 *         @OA\Property(
 *             property="ci",
 *             type="string",
 *             description="Cedula de Identidad (National ID number)",
 *             example="1234567"
 *         ),
 *         @OA\Property(
 *             property="ci_expedition",
 *             type="string",
 *             description="Place of expedition for the CI (e.g., LP for La Paz)",
 *             nullable=true,
 *             example="LP"
 *         ),
 *         @OA\Property(
 *             property="names",
 *             type="string",
 *             description="First name(s) of the individual",
 *             example="Juan Carlos"
 *         ),
 *         @OA\Property(
 *             property="last_names",
 *             type="string",
 *             description="Last name(s) of the individual",
 *             example="Perez Garcia"
 *         ),
 *         @OA\Property(
 *             property="birthdate",
 *             type="string",
 *             format="date",
 *             description="Date of birth",
 *             example="1995-08-15"
 *         ),
 *         @OA\Property(
 *             property="email",
 *             type="string",
 *             format="email",
 *             description="Email address",
 *             example="juan.perez@example.com"
 *         ),
 *         @OA\Property(
 *             property="phone_number",
 *             type="string",
 *             description="Contact phone number",
 *             example="71234567"
 *         ),
 *         @OA\Property(
 *             property="gender",
 *             type="string",
 *             description="Gender of the individual (e.g., Male, Female, Other)",
 *             example="Male"
 *        ),
 *     )
 * )
 */
class Accountables extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = 'personal_data_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_data_id',
    ];

    /**
     * Get the personal data associated with this accountable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalData()
    {
        return $this->belongsTo(
            PersonalData::class,
            'personal_data_id',
            'id'
        );
    }
}
