<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\SubMunicipality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

use function Laravel\Prompts\confirm;

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
        if (!Region::count() !== 0 and confirm('PSGC database is not empty. Do you want to remove all data?')) {
            DB::table('regions')->truncate();
            DB::table('provinces')->truncate();
            DB::table('municipalities')->truncate();
            DB::table('sub_municipalities')->truncate();
            DB::table('barangays')->truncate();

            $this->info('All data has been removed.');
        } else {
            $this->error('Action cancelled.');

            return;
        }

        $this->info('Reading latest PSGC Excel file...');

        $file = storage_path("app/public/psgc/latest.xlsx");

        if (!File::exists($file) && confirm('PSGC file not found. Do you want to download it now?')) {
            $this->info('Downloading latest PSGC file...');
            $this->call('psgc:dl-latest');
        } else {
            $this->error('Action cancelled.');

            return;
        }

        $this->info('Reading PSGC Excel file...');

        $reader = IOFactory::createReader('Xlsx')->setReadDataOnly(true)->setLoadSheetsOnly('PSGC');

        $spreadsheet = $reader->load($file);

        $worksheet = Cache::rememberForever(
            "psgc-latest",
            fn () => $spreadsheet->getSheetByName('PSGC')->rangeToArray('A1:E50000')
        );

        array_shift($worksheet);

        $this->info('Writing to database...');

        // Guide
        // 0 -> code
        // 1 -> name
        // 2 -> old_code
        // 3 -> type
        // 4 -> old_name
        foreach ($worksheet as $row) {
            if ($row[0] === null) {
                continue;
            }

            $type = strtolower($row[3]);

            $data = [
                'code' => $row[0],
                'old_code' => $row[2],
                'region_code' => mb_substr($row[0], 0, 2),
                'province_code' => mb_substr($row[0], 2, 3),
                'municipality_code' => mb_substr($row[0], 2, 5),
                'city_code' => mb_substr($row[0], 2, 5),
                'sub_municipality_code' => mb_substr($row[0], 5, 2),
                'barangay_code' => mb_substr($row[0], 2, 8),
                'name' => $row[1],
                'old_name' => $row[4],
            ];

            DB::transaction(function () use ($type, $data) {
                switch ($type) {
                    case 'reg':
                        Region::create($data);
                        break;
                    case 'prov':
                        Province::create($data);
                        break;
                    case 'mun':
                        Municipality::create($data);
                        break;
                    case 'city':
                        City::create($data);
                        break;
                    case 'submun':
                        SubMunicipality::create($data);
                        break;
                    case 'bgy':
                        Barangay::create($data);
                        break;
                };
            });

            $this->info("Saved to database: $type - {$data['name']}");
        }

        $this->newLine(2);
        $this->info('Done~!');
    }
}
