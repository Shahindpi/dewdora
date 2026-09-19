<?php

namespace Database\Seeders;

use App\Models\AffiliateEvent;
use App\Models\AffiliateProduct;
use Illuminate\Database\Seeder;

class DemoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AffiliateProduct::orderBy('id')->get() as $product) {
            for ($index = 1; $index <= 3; $index++) {
                $session = sprintf('00000000-0000-4000-8000-%012d', $product->id * 10 + $index);
                foreach (['impression', ...($index < 3 ? ['click'] : [])] as $kind) {
                    $event = AffiliateEvent::firstOrCreate([
                        'affiliate_product_id' => $product->id,
                        'session_id' => $session,
                        'kind' => $kind,
                    ], ['brand_id' => $product->brand_id]);
                    if ($event->wasRecentlyCreated) $event->forceFill(['created_at' => now()->subDays($index)])->save();
                }
            }
        }
    }
}
