<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Teachers",
 *     title="Teachers Model",
 *     description="This model represents the teachers in the system.",
 *     required={"personal_data_id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the teacher."),
 *     @OA\Property(property="personal_data_id", type="integer", format="int64", description="The ID of the personal data associated with this teacher.")
 * )
 */
class Teachers extends Model
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
     * Get the personal data associated with this teacher.
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
