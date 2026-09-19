<?php

namespace Tests\Feature;

use App\Models\AffiliateEvent;
use App\Models\AffiliateProduct;
use App\Models\HeroBanner;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeedAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_demo_database_and_first_party_events(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Admin@1234567', $admin->password));
        $this->assertSame('admin', $admin->role->slug);
        $this->assertGreaterThanOrEqual(16, AffiliateProduct::where('status', true)->count());
        $this->assertGreaterThanOrEqual(12, Post::published()->count());
        $this->assertSame(3, HeroBanner::count());
        $this->assertTrue(Storage::disk('public')->exists('uploads/demo/product-1.png'));
        $this->seed();
        $this->assertSame(16, AffiliateProduct::where('status', true)->count());
        $this->assertSame(15, Post::count());
        $this->assertSame(3, HeroBanner::count());
        $this->getJson('/api/v1/public/homepage')->assertOk()
            ->assertJsonCount(16, 'data.carousel_products')
            ->assertJsonPath('data.hero_banners.0.heading', 'Find your next useful tool');
        $product = AffiliateProduct::orderByDesc('created_at')->orderByDesc('id')->firstOrFail();
        $this->getJson('/api/v1/public/products/'.$product->slug)->assertOk()->assertJsonPath('data.product.brand.id', $product->brand_id);
        $post = Post::published()->firstOrFail();
        $this->getJson('/api/v1/public/posts/'.$post->slug)->assertOk()->assertJsonPath('data.post.id', $post->id);
        $session = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
        $payload = ['affiliate_product_id' => $product->id, 'session_id' => $session];
        $this->postJson('/api/v1/public/affiliate-events', $payload + ['kind' => 'impression'])->assertCreated()->assertJsonPath('data.recorded', true);
        $this->postJson('/api/v1/public/affiliate-events', $payload + ['kind' => 'impression'])->assertOk()->assertJsonPath('data.recorded', false);
        $this->postJson('/api/v1/public/affiliate-events', $payload + ['kind' => 'click'])->assertCreated();
        $this->assertSame(1, AffiliateEvent::where('session_id', $session)->where('kind', 'impression')->count());
        $this->assertSame($product->brand_id, AffiliateEvent::where('session_id', $session)->firstOrFail()->brand_id);
        $token = $this->postJson('/api/v1/auth/login', ['email' => 'admin@example.com', 'password' => 'Admin@1234567'])->assertOk()->json('data.token');
        $this->withToken($token)->getJson('/api/v1/admin/affiliate-analytics')->assertOk()->assertJsonPath('data.products.0.id', $product->id);
    }
}
