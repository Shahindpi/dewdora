<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('target_post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('anchor_text', 191);
            $table->string('context', 500)->nullable();
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamps();

            // Explicit short name: MySQL caps identifiers at 64 chars, and
            // Laravel's auto-generated name for this composite unique index
            // would exceed that.
            $table->unique(
                ['source_post_id', 'target_post_id', 'anchor_text'],
                'internal_links_source_target_anchor_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_links');
    }
};
