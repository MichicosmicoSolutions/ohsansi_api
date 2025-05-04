<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OlympiadArea",
 *     title="OlympiadArea",
 *     description="Modelo de la tabla pivote entre Olimpiadas, Áreas y Categorías",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID único del registro"
 *     ),
 *     @OA\Property(
 *         property="olympiad_id",
 *         type="integer",
 *         format="int64",
 *         description="ID de la olimpiada asociada"
 *     ),
 *     @OA\Property(
 *         property="area_id",
 *         type="integer",
 *         format="int64",
 *         description="ID del área asociada"
 *     ),
 *      @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         format="int64",
 *         description="ID de la categoría asociada"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de creación"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de la última actualización"
 *     ),
 *     @OA\Property(
 *         property="area",
 *         description="El área asociada con este registro",
 *         ref="#/components/schemas/Area"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         description="La categoría asociada con este registro",
 *         ref="#/components/schemas/Category"
 *     ),
 *     @OA\Property(
 *         property="olympiad",
 *         description="La olimpiada asociada con este registro",
 *         ref="#/components/schemas/Olympiad"
 *     )
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
