<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $path;
    public $import;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path, Import $import)
    {
        $this->onConnection('redis');
        $this->path = $path;
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        
        $path = Storage::path($this->path);
        $file = fopen($path, 'r');

        $chunkSize = 1000;
        $header = NULL;
        $jobs = [];

        while (($row = fgetcsv($file)) !== FALSE)
        {
            foreach($row as &$column)
            {
                // Remove any invalid or hidden characters
                $column = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $column);
            }
            
            if ($header)
            {
                // Create an associative array
                $rows[] = array_combine($header, $row);

                if (count($rows) === $chunkSize) {
                    $jobs[] = new UpsertProductsJob($rows);
                    $rows = null;
                }

                if (feof($file) && count($rows) > 0) {
                    $jobs[] = new UpsertProductsJob($rows);
                    $rows = null;
                }
            }
            else
            {
                // Store first row as the header
                $header = array_map('strtolower', $row);
            }
        }
        
        fclose($file);
        Storage::delete($this->path);

        $batch = Bus::batch($jobs)->dispatch();

        $this->import->update([
            'batch_id' => $batch->id
        ]);
    }

    public function tags(): array
    {
        return [self::class];
    }
}
