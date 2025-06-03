<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;
use App\Models\BoletaDePago;
use App\Enums\InscriptionStatus;
use App\Models\Inscriptions;

class OCRController extends Controller
{
    public function verificarComprobante(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        try {
            // Guardar la imagen
            $imagen = $request->file('imagen');
            $ruta = $imagen->storeAs('ocr', uniqid() . '.' . $imagen->getClientOriginalExtension(), 'public');
            $path = storage_path('app/public/' . $ruta);

            // Ejecutar OCR
            $ocr = (new TesseractOCR($path))
                ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe') // Ruta a tesseract.exe
                ->lang('eng'); // Puedes cambiar a 'spa' si prefieres español

            $texto = $ocr->run();

            if (trim($texto) === '') {
                return response()->json([
                    'error' => 'No se pudo detectar texto en la imagen. Asegúrate de que la imagen sea clara o utiliza una captura de pantalla.'
                ], 422);
            }

            // Buscar número con prefijo "N°" o similar
            preg_match('/N[°º]?\s*(\d{6,})/', $texto, $matches);
            $numeroDetectado = $matches[1] ?? null;

            if (!$numeroDetectado) {
                return response()->json([
                    'error' => 'No se detectó ningún número válido en la imagen.'
                ], 422);
            }

            // Buscar boleta
            $boleta = BoletaDePago::where('numero_orden_de_pago', $numeroDetectado)->first();

            if (!$boleta) {
                return response()->json([
                    'error' => 'No existe ninguna boleta de pago con ese número.'
                ], 404);
            }

            // Buscar inscripción
            $inscripcion = Inscriptions::where('boleta_de_pago_id', $boleta->id)->first();

            if (!$inscripcion) {
                return response()->json([
                    'error' => 'No se encontró ninguna inscripción asociada a esa boleta de pago.'
                ], 404);
            }

            // Actualizar estado de inscripción
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
