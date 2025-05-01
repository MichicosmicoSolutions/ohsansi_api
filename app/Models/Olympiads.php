<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Olympiads",
 *     title="Olympiads Model",
 *     description="A model representing an Olympiads event.",
 *     required={"title", "description", "price", "status", "Presentation", "Requirements", "start_date", "end_date", "Contactos", "awards"},
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the Olympiads event.", example=1),
 *     @OA\Property(property="title", type="string", description="The title of the Olympiads event.", example="Olympiads Event Title"),
 *     @OA\Property(property="description", type="string", description="A brief description of the Olympiads event.", example="This is a description of the Olympiads event."),
 *     @OA\Property(property="price", type="number", format="float", description="The price associated with the Olympiads event.", example=10.99),
 *     @OA\Property(property="status", type="string", description="The status of the Olympiads event (e.g., 'active', 'inactive').", example="active"),
 *     @OA\Property(property="Presentation", type="string", description="Details about the presentation of the Olympiads event.", example="Presentation details."),
 *     @OA\Property(property="Requirements", type="string", description="The requirements for participating in the Olympiads event.", example="Participants must meet certain requirements."),
 *     @OA\Property(property="start_date", type="string", format="date", description="The start date of the Olympiads event.", example="2023-01-01"),
 *     @OA\Property(property="end_date", type="string", format="date", description="The end date of the Olympiads event.", example="2023-01-07"),
 *     @OA\Property(property="Contactos", type="string", description="Contact information for the Olympiads event.", example="contact@olympiads.com"),
 *     @OA\Property(property="awards", type="string", description="Details about the awards available in the Olympiads event.", example="Gold, Silver, Bronze")
 * )
 */
class Olympiads extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'description',
        'price',
        'presentation',
        'requirements',
        'awards',
        'start_date',
        'end_date',
        'contacts',
    ];

    public function areas()
    {
        return $this->belongsToMany(
            Areas::class,
            'olympiad_areas',
            'olympiad_id',
            'area_id'
        );
    }
    public function categories()
    {
        return $this->belongsToMany(
            Categories::class,
            'category_olympiads',
            'olympiad_id',
            'category_id'
        );
    }
}
