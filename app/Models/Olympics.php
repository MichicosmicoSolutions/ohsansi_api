<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Olympics",
 *     title="Olympics Model",
 *     description="A model representing an Olympics event.",
 *     required={"title", "description", "price", "status", "Presentation", "Requirements", "start_date", "end_date", "Contactos", "awards"},
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the Olympics event.", example=1),
 *     @OA\Property(property="title", type="string", description="The title of the Olympics event.", example="Olympics Event Title"),
 *     @OA\Property(property="description", type="string", description="A brief description of the Olympics event.", example="This is a description of the Olympics event."),
 *     @OA\Property(property="price", type="number", format="float", description="The price associated with the Olympics event.", example=10.99),
 *     @OA\Property(property="status", type="string", description="The status of the Olympics event (e.g., 'active', 'inactive').", example="active"),
 *     @OA\Property(property="Presentation", type="string", description="Details about the presentation of the Olympics event.", example="Presentation details."),
 *     @OA\Property(property="Requirements", type="string", description="The requirements for participating in the Olympics event.", example="Participants must meet certain requirements."),
 *     @OA\Property(property="start_date", type="string", format="date", description="The start date of the Olympics event.", example="2023-01-01"),
 *     @OA\Property(property="end_date", type="string", format="date", description="The end date of the Olympics event.", example="2023-01-07"),
 *     @OA\Property(property="Contactos", type="string", description="Contact information for the Olympics event.", example="contact@olympics.com"),
 *     @OA\Property(property="awards", type="string", description="Details about the awards available in the Olympics event.", example="Gold, Silver, Bronze")
 * )
 */
class Olympics extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'Presentation',
        'Requirements',
        'start_date',
        'end_date',
        'Contactos',
        'awards',
    ];
}
