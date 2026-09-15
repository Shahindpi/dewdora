<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 200);
            $table->string('slug', 220);

            // Public-facing redirect path segment, e.g. /go/{cloaked_slug} -
            // mirrors the affiliate_products link-cloaking pattern.
            $table->string('cloaked_slug', 220)->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('website_url')->nullable();
            $table->text('affiliate_url')->nullable();

            $table->enum('pricing_type', ['free', 'freemium', 'paid', 'subscription', 'contact_sales'])
                ->default('freemium');
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->json('features')->nullable();
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('use_cases')->nullable();

            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('featured_image')->nullable();

            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('click_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->index(['status', 'featured']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tools');
    }
};
