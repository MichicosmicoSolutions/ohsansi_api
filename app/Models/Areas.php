<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Area",
 *     title="Area Model",
 *     description="Represents a knowledge area or domain.",
 *     type="object",
 *     required={"id", "name"},
 *     properties={
 *         @OA\Property(property="id", type="integer", format="int64", example=1),
 *         @OA\Property(property="name", type="string", example="Biology"),
 *         @OA\Property(
 *             property="categories",
 *             type="array",
 *             description="List of categories associated with the area",
 *             @OA\Items(ref="#/components/schemas/Category")
 *         ),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
 *     }
 * )
 */
class Areas extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['name'];

    /**
     * Get the categories associated with this area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->hasMany(
            Categories::class,
            'area_id'
        );
    }

    /**
     * Get the olympiads associated with this area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function olympiads()
    {
        return $this->belongsToMany(
            Olympiads::class,
            'olympiad_areas',
            'area_id',
            'olympiad_id'
        );
    }

    public function olympiadCategories()
    {
        return $this->belongsToMany(
            Categories::class,
            'olympiad_areas',
            'area_id',
            'category_id',
        );
    }
}
