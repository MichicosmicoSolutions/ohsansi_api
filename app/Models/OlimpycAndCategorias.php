<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OlimpycAndCategorias",
 *     title="OlimpycAndCategorias Model",
 *     description="This model represents the relationship between Olympics, Areas, and Categories.",
 *     required={"id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the OlimpycAndCategorias."),
 *     @OA\Property(property="area_id", type="integer", format="int64", description="The area associated with this OlimpycAndCategorias."),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="The category associated with this OlimpycAndCategorias."),
 *     @OA\Property(property="olympic_id", type="integer", format="int64", description="The olympic associated with this OlimpycAndCategorias.")
 * )
 */
class OlimpycAndCategorias extends Model
{
    use HasFactory;

    /**
     * Area associated with this OlimpycAndCategorias.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Areas::class);
    }

    /**
     * Category associated with this OlimpycAndCategorias.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    /**
     * Olympic associated with this OlimpycAndCategorias.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function olympic()
    {
        return $this->belongsTo(Olympics::class);
    }
}
