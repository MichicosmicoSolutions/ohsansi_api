<?php

namespace App\Services;

use App\Models\Olympics;

class OlympicsService
{
    public function getById($id)
    {
        return Olympics::find($id);
    }

    public function create(array $data)
    {
        return Olympics::create($data);
    }

    public function update($id, array $data)
    {
        $olympic = Olympics::find($id);
        if (!$olympic) {
            return null;
        }

        $olympic->update($data);
        return $olympic;
    }
}
