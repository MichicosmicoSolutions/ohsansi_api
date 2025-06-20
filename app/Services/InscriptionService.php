<?php

namespace App\Services;

use App\Enums\InscriptionStatus;
use App\Models\{Competitors, Olympics, PersonalData, Schools, Areas, Categories, Inscriptions, LegalTutors, Accountables};
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class InscriptionService
{
    public function getInscriptions($ci, $code)
    {
        return Inscriptions::with([
            'competitor',
            'competitor.school',
            'competitor.accountable.personalData',
            'competitor.legalTutor.personalData',
            'olympic',
            'area',
            'category',
        ])->whereHas('competitor.accountable.personalData', function ($query) use ($ci) {
            $query->where('ci', $ci);
        })->whereHas('competitor', function ($query) use ($code) {
            $query->whereHas('accountable', function ($query) use ($code) {
                $query->where('code', $code);
            });
        })->get();
    }

    public function getInscriptionById($id, $ci, $code)
    {
        return Inscriptions::with([
            'competitor',
            'competitor.school',
            'competitor.accountable.personalData',
            'competitor.legalTutor.personalData',
            'olympic',
            'area',
            'category',
        ])->whereHas('competitor.accountable.personalData', function ($query) use ($ci) {
            $query->where('ci', $ci);
        })->whereHas('competitor', function ($query) use ($code) {
            $query->whereHas('accountable', function ($query) use ($code) {
                $query->where('code', $code);
            });
        })->findOrFail($id);
    }
}
