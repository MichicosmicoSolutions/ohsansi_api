<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Schools",
 *     title="Schools Model",
 *     description="A model representing a school with name, department, and province.",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier of the school"),
 *     @OA\Property(property="name", type="string", description="The name of the school", example="Example University"),
 *     @OA\Property(property="department", type="string", description="The department of the school", example="Computer Science"),
 *     @OA\Property(property="province", type="string", description="The province where the school is located", example="Ontario"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The timestamp when the school was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The timestamp when the school was last updated")
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
