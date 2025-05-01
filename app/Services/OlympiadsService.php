<?php

namespace App\Services;

use App\Models\Olympiads;

class OlympiadsService
{
    public function getById($id)
    {
        return Olympiads::find($id);
    }

    public function create(array $data)
    {
        return Olympiads::create($data);
    }

    public function update($id, array $data)
    {
        $olympic = Olympiads::find($id);
        if (!$olympic) {
            return null;
        }

        $olympic->update($data);
        return $olympic;
    }
}
