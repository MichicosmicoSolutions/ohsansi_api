<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Responsables",
 *     title="Responsables Model",
 *     description="This model represents the Responsables table in the database.",
 *     required={"personal_data_id", "code"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier of the responsable"),
 *     @OA\Property(property="personal_data_id", type="integer", format="int64", description="The ID of the personal data associated with this responsable"),
 *     @OA\Property(property="code", type="string", description="A code or identifier for the responsable")
 * )
 */
class Responsables extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_data_id',
        'code'
    ];

    /**
     * Get the personal data associated with this responsable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalData()
    {
        return $this->belongsTo(PersonalData::class, 'personal_data_id', 'id');
    }
}
