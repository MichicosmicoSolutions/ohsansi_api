<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Competitors",
 *     title="Competitors Model",
 *     description="Represents a Competitor in the system.",
 *     required={"course", "school_id", "legal_tutor_id", "responsable_id", "personal_data_id"},
 *     @OA\Property(property="course", type="string", example="Computer Science"),
 *     @OA\Property(property="school_id", type="integer", example=1),
 *     @OA\Property(property="legal_tutor_id", type="integer", example=2),
 *     @OA\Property(property="responsable_id", type="integer", example=3),
 *     @OA\Property(property="personal_data_id", type="integer", example=4)
 * )
 */
class Competitors extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course',
        'school_id',
        'legal_tutor_id',
        'responsable_id',
        'personal_data_id',
    ];

    /**
     * Get the school that owns the competitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(Schools::class, 'school_id');
    }

    /**
     * Get the legal tutor that owns the competitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legalTutor()
    {
        return $this->belongsTo(LegalTutors::class, 'legal_tutor_id');
    }

    /**
     * Get the responsable that owns the competitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Responsable()
    {
        return $this->belongsTo(Responsables::class, 'responsable_id');
    }

    /**
     * Get the personal data that owns the competitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id');
    }

    /**
     * Get the inscriptions for the competitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class, 'competitor_id');
    }
}
