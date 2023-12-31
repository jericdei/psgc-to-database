<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class GetLatestPSGC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'psgc:dl-latest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads the latest PSGC Excel file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Visiting PSGC website...');

        $psgcUrl = 'https://psa.gov.ph/classification/psgc';

        $html = Http::get($psgcUrl)->body();

        $crawler = new Crawler($html);

        $link = $crawler->filterXPath('//a[text()="Publication"]')->first();

        $spinner = $this->spinner();

        $spinner->setMessage('Downloading latest PSGC file...');
        $spinner->start();

        $excel = Http::connectTimeout(999999)
            ->withOptions([
                'progress' => fn () => $spinner->advance()
            ])
            ->get($link->attr('href'))
            ->body();

        $spinner->finish();
        $this->newLine();

        $this->info('Storing the file...');

        Storage::disk('public')->put("psgc/latest.xlsx", $excel);

        $this->info('PSGC file has been downloaded successfully!');
    }
}
