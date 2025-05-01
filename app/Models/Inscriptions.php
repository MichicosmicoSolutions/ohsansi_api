<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Inscriptions",
 *     title="Inscriptions Model",
 *     description="Represents an inscription in the system.",
 *     required={"competitor_id", "olympiad_id", "area_id", "category_id", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, description="The unique identifier for the inscription."),
 *     @OA\Property(property="competitor_id", type="integer", format="int64", description="The ID of the competitor associated with the inscription."),
 *     @OA\Property(property="olympiad_id", type="integer", format="int64", description="The ID of the Olympiad event associated with the inscription."),
 *     @OA\Property(property="area_id", type="integer", format="int64", description="The ID of the area associated with the inscription."),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="The ID of the category associated with the inscription."),
 *     @OA\Property(property="status", type="string", description="The status of the inscription.")
 * )
 */
class Inscriptions extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'paid_at',
        'drive_url',
        'school_id',
        'competitor_data_id',
        'responsable_id',
        'legal_tutor_id',
        'olympiad_id'
    ];

    /**
     * Get the competitor associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function competitor_data()
    {
        return $this->belongsTo(
            PersonalData::class,
            'competitor_data_id'
        );
    }

    public function responsable()
    {
        return $this->belongsTo(
            Responsables::class,
            'responsable_id',
            'personal_data_id'
        );
    }

    public function legalTutor()
    {
        return $this->belongsTo(
            LegalTutors::class,
            'legal_tutor_id',
            'personal_data_id'
        );
    }

    /**
     * Get the Olympiads event associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function olympiad()
    {
        return $this->belongsTo(
            Olympiads::class,
            'olympiad_id'
        );
    }
}
