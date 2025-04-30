<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Inscriptions",
 *     title="Inscriptions Model",
 *     description="Represents an inscription in the system.",
 *     required={"competitor_id", "olympic_id", "area_id", "category_id", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, description="The unique identifier for the inscription."),
 *     @OA\Property(property="competitor_id", type="integer", format="int64", description="The ID of the competitor associated with the inscription."),
 *     @OA\Property(property="olympic_id", type="integer", format="int64", description="The ID of the Olympic event associated with the inscription."),
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
        'competitor_id',
        'olympic_id',
        'area_id',
        'category_id',
        'status',
    ];

    /**
     * Get the competitor associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function competitor()
    {
        return $this->belongsTo(Competitors::class, 'competitor_id');
    }

    /**
     * Get the Olympic event associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function olympic()
    {
        return $this->belongsTo(Olympics::class, 'olympic_id');
    }

    /**
     * Get the area associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
    }

    /**
     * Get the category associated with the inscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}
