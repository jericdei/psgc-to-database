<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Console\Command;
use GuzzleHttp\Middleware;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Prompts\Spinner;
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

        $excel = Http::connectTimeout(100)
            ->withOptions([
                'progress' => fn () => $spinner->advance()
            ])
            ->get($link->attr('href'))
            ->body();

        $spinner->finish();
        $this->newLine();

        $now = now()->format('Y-m');
        $this->info('Storing the file...');

        Storage::disk('public')->put("psgc/{$now}.xlsx", $excel);

        $this->info('PSGC file downloaded successfully. Check storage/app/public/psgc');
    }
}
