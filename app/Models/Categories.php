<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Areas;

/**
 * @OA\Schema(
 *     schema="Categories",
 *     title="Categories Model",
 *     description="This model represents the Categories table in the database.",
 *     required={"id"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the category."),
 *     @OA\Property(property="name", type="string", description="The name of the category."),
 *     @OA\Property(property="area_id", type="integer", format="int64", description="The ID of the area to which this category belongs."),
 *     @OA\Property(property="range_course", type="array", @OA\Items(type="string"), description="An array representing the range of courses available in this category.", example={"1ro Primaria", "2do Primaria", "3ro Primaria", "4to Primaria", "5to Primaria", "6to Primaria", "1ro Secundaria", "2do Secundaria", "3ro Secundaria", "4to Secundaria", "5to Secundaria", "6to Secundaria"}),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The timestamp when the category was created."),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The timestamp when the category was last updated.")
 * )
 */
class Categories extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $casts = [
        'range_course' => 'array',
    ];
    public function area()
    {
        return $this->belongsTo(
            Areas::class,
            'area_id'
        );
    }

    public function olympiads()
    {
        return $this->belongsToMany(
            Olympiads::class,
            'olympiad_areas',
            'category_id',
            'olympiad_id'
        );
    }
}
