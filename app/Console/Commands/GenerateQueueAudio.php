<?php

namespace App\Console\Commands;

use App\Models\Gate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateQueueAudio extends Command
{
    protected $signature = 'queue:audio:generate
                            {--force : Overwrite existing files}
                            {--gate=* : Regenerate specific gate IDs only}';

    protected $description = 'Download and generate pre-recorded Indonesian audio segments for the offline queue announcement system';

    protected string $outputDir;

    protected array $digits = [
        '0' => 'nol',
        '1' => 'satu',
        '2' => 'dua',
        '3' => 'tiga',
        '4' => 'empat',
        '5' => 'lima',
        '6' => 'enam',
        '7' => 'tujuh',
        '8' => 'delapan',
        '9' => 'sembilan',
    ];

    protected array $letters = [
        'a' => 'A',      'b' => 'Be',     'c' => 'Ce',     'd' => 'De',
        'e' => 'E',      'f' => 'Ef',     'g' => 'Ge',     'h' => 'Ha',
        'i' => 'I',      'j' => 'Je',     'k' => 'Ka',     'l' => 'El',
        'm' => 'Em',     'n' => 'En',     'o' => 'O',      'p' => 'Pe',
        'q' => 'Ki',     'r' => 'Er',     's' => 'Es',     't' => 'Te',
        'u' => 'U',      'v' => 'Fe',     'w' => 'We',     'x' => 'Eks',
        'y' => 'Ye',     'z' => 'Zet',
    ];

    protected array $phrases = [
        'phrase_nomor-antrian'     => 'nomor antrian',
        'phrase_silakan-menuju-ke' => 'silakan menuju ke',
    ];

    public function handle(): int
    {
        $this->outputDir = public_path('sounds/queue');

        if (! is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
            $this->line("  <comment>Created directory:</comment> public/sounds/queue/");
        }

        $force      = $this->option('force');
        $gateFilter = $this->option('gate');

        if (empty($gateFilter)) {
            $this->downloadPhrases($force);
            $this->downloadLetters($force);
            $this->downloadDigits($force);
        }

        $this->downloadGates($force, $gateFilter);

        $this->newLine();
        $this->info('✅ Audio generation complete.');
        $this->line('  Run the web server and open the admin panel to test.');

        return self::SUCCESS;
    }

    protected function downloadPhrases(bool $force): void
    {
        $this->info('Downloading fixed phrases…');
        foreach ($this->phrases as $filename => $text) {
            $this->downloadSegment($filename, $text, $force);
        }
    }

    protected function downloadLetters(bool $force): void
    {
        $this->info('Downloading letters A–Z…');
        foreach ($this->letters as $char => $pronunciation) {
            $this->downloadSegment("letter_{$char}", $pronunciation, $force);
        }
    }

    protected function downloadDigits(bool $force): void
    {
        $this->info('Downloading digits 0–9…');
        foreach ($this->digits as $digit => $pronunciation) {
            $this->downloadSegment("digit_{$digit}", $pronunciation, $force);
        }
    }

    protected function downloadGates(bool $force, array $gateFilter): void
    {
        $query = Gate::query();

        if (! empty($gateFilter)) {
            $query->whereIn('id', $gateFilter);
        }

        $gates = $query->get();

        if ($gates->isEmpty()) {
            $this->warn('  No gates found in database — skipping gate audio.');
            return;
        }

        $this->info('Downloading gate name audio…');

        foreach ($gates as $gate) {
            $this->downloadSegment("gate_{$gate->id}", $gate->name, $force);
        }
    }

    protected function downloadSegment(string $filename, string $text, bool $force): void
    {
        $path = "{$this->outputDir}/{$filename}.mp3";

        if (! $force && file_exists($path)) {
            $this->line("  <fg=gray>SKIP  {$filename}.mp3 (already exists)</>");
            return;
        }

        $url = 'https://translate.google.com/translate_tts?' . http_build_query([
            'ie'       => 'UTF-8',
            'q'        => $text,
            'tl'       => 'id',
            'client'   => 'tw-ob',
            'ttsspeed' => '0.9',
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer'         => 'https://translate.google.com/',
                'Accept'          => 'audio/mpeg, audio/*, */*',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8',
            ])->timeout(15)->get($url);

            if ($response->successful() && strlen($response->body()) > 512) {
                file_put_contents($path, $response->body());
                $size = round(filesize($path) / 1024, 1);
                $this->line("  <info>OK</info>    {$filename}.mp3  ({$size} KB)  <fg=gray>← {$text}</>");
            } else {
                $this->error("  FAIL  {$filename}.mp3 — HTTP {$response->status()} or empty body");
            }
        } catch (\Exception $e) {
            $this->error("  FAIL  {$filename}.mp3 — {$e->getMessage()}");
        }

        usleep(300_000);
    }
}
