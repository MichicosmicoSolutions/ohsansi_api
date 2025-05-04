<?php

namespace App\Validators;

use App\Validators\Contracts\ValidatesInput;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SelectedAreaRequest",
 *     title="Selected Area Data",
 *     description="Data for creating or updating a selected area.",
 *     required={"area_id", "category_id"},
 *     @OA\Property(
 *         property="area_id",
 *         type="integer",
 *         format="int64",
 *         example=1,
 *         description="The ID of the area."
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         format="int64",
 *         example=2,
 *         description="The ID of the category."
 *     ),
 *     @OA\Property(
 *         property="academic_tutor",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PersonalDataRequest"),
 *         description="Information about the academic tutor, if applicable."
 *     )
 * )
 */
class SelectedAreaValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return
            [
                "{$dot}area_id" => 'required|integer',
                "{$dot}category_id" => 'required|integer',
                "{$dot}academic_tutor" => 'sometimes|array',
            ] +
            PersonalDataValidator::rules("{$dot}academic_tutor");
    }

    public static function messages(string $prefix = ""): array
    {
        $dot = $prefix ? $prefix . "." : "";
        return [
            "{$dot}area_id.required" => 'El campo área es obligatorio.',
            "{$dot}area_id.integer" => 'El campo área debe ser un número entero.',
            "{$dot}academic_tutor.array" => 'El campo tutor académico debe ser un array.'
        ] + PersonalDataValidator::messages("{$dot}academic_tutor");
    }
}
