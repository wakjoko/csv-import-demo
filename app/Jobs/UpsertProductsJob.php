<?php

namespace App\Jobs;

use App\Models\Product;
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

    public $products;

    /**
     * Create a new job instance.
     */
    public function __construct($products)
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

        DB::transaction(function() use ($products) {
            Product::query()->lockForUpdate()->upsert($products, 'unique_key');
        }, 2);
    }

    public function tags(): array
    {
        return [self::class];
    }
}
