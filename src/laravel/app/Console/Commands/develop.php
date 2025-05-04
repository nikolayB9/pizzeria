<?php

namespace App\Console\Commands;

use App\Enums\Category\CategoryTypeEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;

class develop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:develop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productVariant = ProductVariant::where('id', 1)
            ->select('id', 'price', 'product_id')
            ->first();

        $category = $productVariant->product
            ->categories()
            ->where('type', CategoryTypeEnum::ProductType->value)
            ->select('id', 'slug')
            ->firstOrFail();
dd(config('cart'));

        dump($category);
    }
}
