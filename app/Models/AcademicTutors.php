<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AcademicTutors",
 *     title="Academic Tutors Model",
 *     description="This model represents the academic tutors in the system.",
 *     required={"personal_data_id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the academic tutor."),
 *     @OA\Property(property="personal_data_id", type="integer", format="int64", description="The ID of the personal data associated with this academic tutor.")
 * )
 */
class AcademicTutors extends Model
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
     * Get the personal data associated with this academic tutor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id', 'id');
    }
}
