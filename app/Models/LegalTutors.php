<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="LegalTutors",
 *     title="Legal Tutors Model",
 *     description="This model represents the Legal Tutors entity in the application.",
 *     required={"personal_data_id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the legal tutor."),
 *     @OA\Property(property="personal_data_id", type="integer", format="int64", description="The ID of the associated personal data.")
 * )
 */
class LegalTutors extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_data_id',
    ];

    /**
     * Define the relationship with PersonalData model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id', 'id');
    }
}
