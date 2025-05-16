<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Inscription",
 *     title="Inscription",
 *     description="Inscription model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Primary Key"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the inscription (e.g., pending, approved, rejected)"
 *     ),
 *     @OA\Property(
 *         property="drive_url",
 *         type="string",
 *         format="url",
 *         nullable=true,
 *         description="URL to related documents in Google Drive"
 *     ),
 *     @OA\Property(
 *         property="school_id",
 *         type="integer",
 *         format="int64",
 *         description="Foreign key referencing the Schools table"
 *     ),
 *     @OA\Property(
 *         property="competitor_data_id",
 *         type="integer",
 *         format="int64",
 *         description="Foreign key referencing the PersonalData table for the competitor"
 *     ),
 *      @OA\Property(
 *         property="accountable_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="Foreign key referencing the PersonalData ID in the Accountables table"
 *     ),
 *     @OA\Property(
 *         property="legal_tutor_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="Foreign key referencing the PersonalData ID in the LegalTutors table"
 *     ),
 *     @OA\Property(
 *         property="olympiad_id",
 *         type="integer",
 *         format="int64",
 *         description="Foreign key referencing the Olympiads table"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     ),
 *     @OA\Property(
 *         nullable=true,
 *         property="competitor_data",
 *         ref="#/components/schemas/PersonalData",
 *         description="Competitor's personal data associated with this inscription"
 *     ),
 *      @OA\Property(
 *         nullable=true,
 *         property="accountable",
 *         ref="#/components/schemas/Accountable",
 *         description="Accountable person associated with this inscription"
 *     ),
 *      @OA\Property(
 *         nullable=true,
 *         property="legalTutor",
 *         ref="#/components/schemas/LegalTutor",
 *         description="Legal tutor associated with this inscription"
 *     ),
 *     @OA\Property(
 *         nullable=true,
 *         property="school",
 *         ref="#/components/schemas/School",
 *         description="School associated with this inscription"
 *     ),
 *     @OA\Property(
 *         nullable=true,
 *         property="selected_areas",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SelectedArea"),
 *         description="Areas selected for this inscription"
 *     )
 * )
 */
class Inscriptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'drive_url',
        'school_id',
        'competitor_data_id',
        'accountable_id',
        'legal_tutor_id',
        'olympiad_id'
    ];

    public function competitor_data()
    {
        return $this->belongsTo(
            PersonalData::class,
            'competitor_data_id'
        );
    }

    public function accountable()
    {
        return $this->belongsTo(
            Accountables::class,
            'accountable_id',
            'personal_data_id'
        );
    }

    public function legalTutor()
    {
        return $this->belongsTo(
            LegalTutors::class,
            'legal_tutor_id',
            'personal_data_id'
        );
    }

    public function olympiad()
    {
        return $this->belongsTo(
            Olympiads::class,
            'olympiad_id'
        );
    }

    public function school()
    {
        return $this->belongsTo(
            Schools::class,
            'school_id',
            'id'
        );
    }

    public function selected_areas()
    {
        return $this->hasMany(
            SelectedAreas::class,
            'inscription_id',
            'id'
        );
    }
    
    public function area()
    {
        return $this->belongsTo(Areas::class, 'area_id');
    }
    
}
