<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OlympiadAreas",
 *     title="OlympiadAreas Model",
 *     description="This model represents the relationship between Olympics, Areas, and Categories.",
 *     required={"id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the OlympiadAreas."),
 *     @OA\Property(property="area_id", type="integer", format="int64", description="The area associated with this OlympiadAreas."),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="The category associated with this OlympiadAreas."),
 *     @OA\Property(property="olympic_id", type="integer", format="int64", description="The olympic associated with this OlympiadAreas.")
 * )
 */
class OlympiadAreas extends Model
{
    use HasFactory;

    /**
     * Area associated with this OlympiadAreas.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(
            Areas::class,
            'area_id',
        );
    }

    /**
     * Category associated with this OlympiadAreas.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(
            Categories::class,
            'category_id',
        );
    }

    /**
     * Olympic associated with this OlympiadAreas.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function olympiad()
    {
        return $this->belongsTo(
            Olympiads::class,
            'olympiad_id',
        );
    }
}
