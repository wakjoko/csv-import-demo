<?php

namespace App\Jobs;

use App\Models\Product;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpsertProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $maxTries = 10;
    public $products;

    /**
     * Create a new job instance.
     */
    public function __construct(array $products)
    {
        $this->onConnection('redis');
        $this->products = $products;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        
        $columns = (new Product())->getFillable();
        $products = [];

        foreach ($this->products as $product) {
            $filtered = collect($product)->only($columns)->toArray();

            $products[] = $filtered;
        }

        // another way of preventing deadlocks is by using retry()
        // but here we will retry the job again when insert fails
        try {
            DB::transaction(fn() =>
                Product::query()->upsert($products, 'unique_key')
            );
        } catch (Exception $exception) {
            if ($this->attempts() > 5) {
                // mark as failed after 5 tries
                throw $exception;
            }

            // retry after 30 seconds
            $this->release(30);
            return;
        }
    }

    public function tags(): array
    {
        return [self::class];
    }
}
