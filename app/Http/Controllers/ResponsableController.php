<?php

namespace App\Http\Controllers;

use App\Models\Accountables;
use App\Services\ResponsableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponsableController extends Controller
{
    /**
     * @deprecated
     * Responsable access 
     * @OA\Post(
     *     path="/api/accountable/access",
     *     summary="Access to accountable data with JWT token",
     *     description="Authenticate a accountable by CI and code, then generate a JWT token.",
     *     operationId="access",
     *     tags={"Responsable"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credentials to access accountable data",
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

        $accountable = Accountables::with("personalData")->whereHas("personalData", function ($query) use ($ci) {
            $query->where("ci", $ci);
        })->first();

        if (!$accountable) {
            return response()->json([
                "error" => "Responsable not found",
            ], 404);
        }

        if ($accountable->code !== $request->input("code")) {
            return response()->json([
                "error" => "Invalid code",
            ], 401);
        }

        $token = ResponsableService::generateJWT($accountable->personalData->ci, $accountable->code);
        return response()->json([
            "token" => $token,
        ], 200);
    }
}
