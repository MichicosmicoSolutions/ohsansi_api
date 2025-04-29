<?php

namespace App\Http\Controllers;

use App\Models\Responsables;
use App\Services\ResponsableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponsableController extends Controller
{
    /**
     * Responsable access 
     * @OA\Post(
     *     path="/api/responsable/access",
     *     summary="Access to responsable data with JWT token",
     *     description="Authenticate a responsable by CI and code, then generate a JWT token.",
     *     operationId="access",
     *     tags={"Responsable"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credentials to access responsable data",
     *         @OA\JsonContent(
     *             required={"ci","code"},
     *             @OA\Property(property="ci", type="integer", example=12345678),
     *             @OA\Property(property="code", type="string", example="NFvyeVUh fEChWv2q J2zu3xYd RfVYrsGB njvFM92m")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="generated.jwt.token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid code",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Responsable not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Responsable not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string", example="Error message")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function access(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "ci" => "required|integer",
            "code" => "required|string",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        $ci = $request->input("ci");

        $responsable = Responsables::with("personalData")->whereHas("personalData", function ($query) use ($ci) {
            $query->where("ci", $ci);
        })->first();

        if (!$responsable) {
            return response()->json([
                "error" => "Responsable not found",
            ], 404);
        }

        if ($responsable->code !== $request->input("code")) {
            return response()->json([
                "error" => "Invalid code",
            ], 401);
        }

        $token = ResponsableService::generateJWT($responsable->personalData->ci, $responsable->code);
        return response()->json([
            "token" => $token,
        ], 200);
    }
}
