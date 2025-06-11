<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InscriptionExcelService
{
    public static function generateExcel(string $filePath, $areas): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Areas');
        $sheet2->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $sheet2->setCellValue('A1', 'Name');
        $sheet2->setCellValue('B1', 'area_id');
        $sheet2->setCellValue('C1', 'category_id');

        foreach ($areas as $index => $area) {
            $sheet2->setCellValue('A' . ($index + 2), $area['area']['name'] . ' - ' . $area['category']['name']);
            $sheet2->setCellValue('B' . ($index + 2), $area['area_id']);
            $sheet2->setCellValue('C' . ($index + 2), $area['category_id']);
        }

        $sheet->setCellValue('A1', 'Nombres');
        $sheet->setCellValue('B1', 'Apellidos');
        $sheet->setCellValue('C1', 'CI');
        $sheet->setCellValue('D1', 'Lugar de expedición de CI');
        $sheet->setCellValue('E1', 'Cumpleaños');
        $sheet->setCellValue('F1', 'Correo');
        $sheet->setCellValue('G1', 'Celular');
        $sheet->setCellValue('H1', 'Genero');

        $sheet->setCellValue('I1', 'Nombre del tutor');
        $sheet->setCellValue('J1', 'Apellido del tutor');
        $sheet->setCellValue('K1', 'CI del tutor');
        $sheet->setCellValue('L1', 'Lugar de expedición del tutor');
        $sheet->setCellValue('M1', 'Correo del tutor');
        $sheet->setCellValue('N1', 'Celular del tutor');
        $sheet->setCellValue('O1', 'Género del tutor');

        $sheet->setCellValue('P1', 'Area y categoría');
        $sheet->setCellValue('Q1', 'Nombre del profesor guía');
        $sheet->setCellValue('R1', 'Apellido del profesor guía');
        $sheet->setCellValue('S1', 'CI del profesor guía');
        $sheet->setCellValue('T1', 'Lugar de expedición del profesor guía');
        $sheet->setCellValue('U1', 'Correo del profesor guía');
        $sheet->setCellValue('V1', 'Celular del profesor guía');
        $sheet->setCellValue('W1', 'Género del profesor guía');


        // Crear validación de datos (lista desplegable) para la columna P
        $validation = $sheet->getCell('P2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Entrada inválida');
        $validation->setError('El valor no está en la lista.');
        $validation->setPromptTitle('Selecciona un área y categoría');
        $validation->setPrompt('Por favor selecciona de la lista');
        $validation->setFormula1("'Areas'!\$A\$2:\$A\$" . (count($areas) + 1));

        for ($row = 2; $row <= 100; $row++) {
            $cell = $sheet->getCell("P$row");
            $cell->setDataValidation(clone $validation);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
