<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoletaDePago;
use App\Enums\InscriptionStatus;
use App\Models\Inscriptions;
use Illuminate\Support\Facades\DB;

class OCRController extends Controller
{
    public function verificarComprobante(Request $request)
    {
        $request->validate([
            'numero_orden_de_pago' => 'required|string'
        ]);

        $numero = $request->input('numero_orden_de_pago');
        $numeroLimpio = ltrim($numero, '0');



        $boleta = BoletaDePago::where('numero_orden_de_pago', $numero)
            ->orWhere('numero_orden_de_pago', $numeroLimpio)
            ->first();

        if (!$boleta) {
            return response()->json([
                'error' => 'No existe ninguna boleta de pago con ese número.',
                'numero_recibido' => $numero
            ], 404);
        }

        $inscripcions = Inscriptions::where('boleta_de_pago_id', $boleta->id)->get();

        if ($inscripcions->isEmpty()) {
            return response()->json([
                'error' => 'No se encontró ninguna inscripción asociada a esa boleta de pago.',
                'numero_recibido' => $numero
            ], 404);
        }

        foreach ($inscripcions as $inscripcion) {
            if ($inscripcion->status !== InscriptionStatus::PENDING) {
                return response()->json([
                    'error' => 'La inscripción ya ha sido procesada.',
                    'numero_recibido' => $numero
                ], 400);
            }
        }

        DB::transaction(function () use ($inscripcions, $boleta) {
            foreach ($inscripcions as $inscripcion) {
                $inscripcion->status = InscriptionStatus::COMPLETED;
                $inscripcion->save();
            }

            $boleta->status = 'completed';
            $boleta->save();
        });
        return response()->json([
            'mensaje' => 'El estado de la inscripción y la boleta de pago han sido actualizados a COMPLETED.',
            'numero_detectado' => $numero
        ]);
    }
}
