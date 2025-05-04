<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="School",
 *     type="object",
 *     title="School Model",
 *     description="Represents a school entry.",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The unique identifier for the school.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the school.",
 *         example="Marista"
 *     ),
 *     @OA\Property(
 *         property="department",
 *         type="string",
 *         enum={"Cochabamba", "La Paz", "Oruro", "Potosi", "Tarija", "Santa Cruz", "Beni", "Pando"},
 *         description="The department the school belongs to.",
 *         example="Cochabamba"
 *     ),
 *     @OA\Property(
 *         property="province",
 *         type="string",
 *         description="The province where the school is located.",
 *         example="Cercado"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the school was created.",
 *         example="2023-05-15T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the school was last updated.",
 *         example="2023-05-15T12:30:00Z"
 *     )
 * )
 */
class Schools extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'department',
        'province',
    ];
}
