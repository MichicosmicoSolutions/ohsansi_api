<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SelectedArea",
 *     title="Selected Area",
 *     description="Represents an area selected within an inscription, potentially associated with a teacher and category.",
 *     required={"inscription_id", "area_id"},
 *     @OA\Property(
 *         property="id",
 *         description="Unique identifier for the selected area record",
 *         type="integer",
 *         format="int64",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="paid_at",
 *         description="Timestamp when the area selection was paid for (if applicable)",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2023-10-27T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="inscription_id",
 *         description="Foreign key referencing the inscription",
 *         type="integer",
 *         format="int64",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="area_id",
 *         description="Foreign key referencing the area",
 *         type="integer",
 *         format="int64",
 *         example=12
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         description="Foreign key referencing the category (optional)",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="teacher_id",
 *         description="Foreign key referencing the teacher (optional, linked via personal_data_id)",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         example=8
 *     ),
 *     @OA\Property(
 *          property="area",
 *          description="The related area",
 *          nullable=true,
 *          ref="#/components/schemas/Area"
 *     ),
 *     @OA\Property(
 *          property="category",
 *          description="The related category",
 *          nullable=true,  
 *          ref="#/components/schemas/Category"
 *     ),
 *     @OA\Property(
 *          property="teacher",
 *          description="The related teacher",
 *          nullable=true,
 *          ref="#/components/schemas/Teacher"
 *     )
 * )
 */
class SelectedAreas extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['inscription_id', 'area_id'];


    protected $fillable = [
        "paid_at",
        "inscription_id",
        "area_id",
        "category_id",
        "teacher_id",
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscriptions::class, 'inscription_id');
    }

    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function teacher()
    {
        return $this->belongsTo(
            Teachers::class,
            'teacher_id',
            'personal_data_id'
        );
    }
}
