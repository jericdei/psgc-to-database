<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ConvertToDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'psgc:convert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert PSGC excel data to database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m');

        $file = storage_path("app/public/psgc/$now.xlsx");

        $reader = IOFactory::createReader('Xlsx')->setReadDataOnly(true)->setLoadSheetsOnly('PSGC');

        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getSheetByName('PSGC');

        foreach ($worksheet->getRowIterator() as $row) {
            if ($row->getRowIndex() === 1) {
                continue;
            }

            foreach ($row->getCellIterator() as $cell) {
                dump($cell->getValue());
            }

            dd('oops');
        }

        dd($spreadsheet);
    }
}
