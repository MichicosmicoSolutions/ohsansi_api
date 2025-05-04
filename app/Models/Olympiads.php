<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Olympiad",
 *     type="object",
 *     title="Olympiad Model",
 *     description="Represents an olympiad competition or event.",
 *     required={"title", "start_date", "end_date"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Primary Key",
 *         readOnly=true,
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="The official title of the olympiad",
 *         maxLength=255,
 *         example="International Mathematics Olympiad 2024"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Current status (e.g., 'draft', 'published', 'archived', 'ongoing', 'finished')",
 *         example="published"
 *     ),
 *     @OA\Property(
 *         property="publish",
 *         type="boolean",
 *         description="Indicates if the olympiad is publicly visible",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         format="text",
 *         description="Detailed description of the olympiad",
 *         example="An annual competition for high school students focused on advanced mathematical problems."
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Registration fee or cost associated with the olympiad",
 *         example=50.00
 *     ),
 *     @OA\Property(
 *         property="presentation",
 *         type="string",
 *         format="text",
 *         description="Information about the presentation format or link to presentation materials",
 *         example="Includes online tests and a final presentation round. Details: [link]"
 *     ),
 *     @OA\Property(
 *         property="requirements",
 *         type="string",
 *         format="text",
 *         description="Eligibility criteria and requirements for participants",
 *         example="Must be a student under 20 years old, not enrolled in university."
 *     ),
 *     @OA\Property(
 *         property="awards",
 *         type="string",
 *         format="text",
 *         description="Description of the prizes and awards offered",
 *         example="Gold, Silver, Bronze medals. Top 3 receive scholarships."
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date-time",
 *         description="The official start date and time of the olympiad (or registration period)",
 *         example="2024-09-01T00:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date-time",
 *         description="The official end date and time of the olympiad (or registration period)",
 *         example="2024-11-30T23:59:59Z"
 *     ),
 *     @OA\Property(
 *         property="contacts",
 *         type="string",
 *         format="text",
 *         description="Contact information for inquiries about the olympiad",
 *         example="Email: contact@imo2024.org, Phone: +1-555-123-4567"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the record was created",
 *         readOnly=true,
 *         example="2023-10-01T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the record was last updated",
 *         readOnly=true,
 *         example="2023-10-05T12:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="areas",
 *         type="array",
 *         description="List of subject areas associated with the olympiad",
 *         @OA\Items(ref="#/components/schemas/Area")
 *     ),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         description="List of categories the olympiad belongs to (e.g., age group, difficulty)",
 *         @OA\Items(ref="#/components/schemas/Category")
 *     )
 * )
 */
class Olympiads extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'publish',
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
