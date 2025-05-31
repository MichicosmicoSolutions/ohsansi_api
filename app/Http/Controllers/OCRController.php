<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;
use App\Models\BoletaDePago;
use App\Enums\InscriptionStatus;
use App\Models\Inscriptions;
use Illuminate\Support\Facades\Log;

class OCRController extends Controller
{
    public function verificarComprobante(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        try {

            $imagen = $request->file('imagen');
            $ruta = $imagen->storeAs('ocr', uniqid() . '.' . $imagen->getClientOriginalExtension(), 'public');
            $path = storage_path('app/public/' . $ruta);


            $ocr = (new TesseractOCR($path))
                ->executable('/usr/bin/tesseract')
                ->lang('eng');

            $texto = $ocr->run();

            if (trim($texto) === '') {
                return response()->json([
                    'error' => 'No se pudo detectar texto en la imagen. Asegúrate de que la imagen sea clara o utiliza una captura de pantalla.'
                ], 422);
            }

            preg_match('/\b\d{6,}\b/', $texto, $matches);
            $numeroDetectado = $matches[0] ?? null;

            Log::info("Texto detectado: " . $texto);

            if (!$numeroDetectado) {
                return response()->json([
                    'error' => 'No se detectó ningún número válido en la imagen.'
                ], 422);
            }

            $boleta = BoletaDePago::where('numero_orden_de_pago', $numeroDetectado)->first();

            if (!$boleta) {
                return response()->json([
                    'error' => 'No existe ninguna boleta de pago con ese número.'
                ], 404);
            }

            $inscripcion = Inscriptions::where('boleta_de_pago_id', $boleta->id)->first();

            if (!$inscripcion) {
                return response()->json([
                    'error' => 'No se encontró ninguna inscripción asociada a esa boleta de pago.'
                ], 404);
            }


            $inscripcion->status = InscriptionStatus::COMPLETED;
            $inscripcion->save();

            return response()->json([
                'mensaje' => 'Estado de inscripción actualizado a COMPLETED.',
                'texto_completo' => $texto,
                'numero_detectado' => $numeroDetectado
            ]);
        } catch (UnsuccessfulCommandException $e) {
            return response()->json([
                'error' => 'Intenta con una imagen más clara.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado: ' . $e->getMessage()
            ], 500);
        }
    }
}
