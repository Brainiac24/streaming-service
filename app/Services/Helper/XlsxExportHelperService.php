<?php

namespace App\Services\Helper;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;

class XlsxExportHelperService
{
    public function __construct()
    {
    }

    public function exportFile($data, $fileName)
    {
        ini_set('memory_limit', '128M');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $resource = $data;
        $sheet->fromArray($resource['data'], NULL, 'A1');

        $writer = new Xlsx($spreadsheet);
        $datedir = Carbon::now()->format("dmY");
        File::makeDirectory(storage_path("app/local/xlsx/") . $datedir, 0777, true, true);

        $file = $datedir . $fileName;
        $writer->save(storage_path("app/local/xlsx/") . $file);

        return $file;
    }
}
