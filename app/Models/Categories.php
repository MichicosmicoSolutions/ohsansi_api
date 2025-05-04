<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Areas;
use App\Models\Olympiads;

/**
 * @OA\Schema(
 *     schema="Category",
 *     title="Category Model",
 *     description="This model represents the Categories table in the database.",
 *     required={"id", "name", "area_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The unique identifier for the category.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the category.",
 *         example="Matemáticas Primaria"
 *     ),
 *     @OA\Property(
 *         property="area_id",
 *         type="integer",
 *         format="int64",
 *         description="The ID of the area to which this category belongs.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="range_course",
 *         type="array",
 *         @OA\Items(type="string"),
 *         description="An array representing the range of courses available in this category.",
 *         example={"1ro Primaria", "2do Primaria", "3ro Primaria", "4to Primaria", "5to Primaria", "6to Primaria"}
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The timestamp when the category was created (if timestamps are enabled).",
 *         example="2023-01-01T12:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The timestamp when the category was last updated (if timestamps are enabled).",
 *         example="2023-01-01T13:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *          property="area",
 *          description="The Area associated with this Category.",
 *          ref="#/components/schemas/Area"
 *     )
 * )
 */
class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'area_id',
        'range_course'
    ];

    protected $casts = [
        'range_course' => 'array',
    ];

    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
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
