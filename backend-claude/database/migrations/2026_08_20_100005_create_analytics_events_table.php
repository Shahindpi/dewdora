<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', [
                'page_view', 'affiliate_click', 'ai_tool_click',
                'newsletter_signup', 'search', 'outbound_click',
            ])->index();

            $table->nullableMorphs('trackable');

            $table->string('url', 2048)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('user_agent')->nullable();
            // Salted hash, never the raw IP, for privacy-friendly rough
            // unique-visitor/session analysis.
            $table->string('ip_hash', 64)->nullable();
            $table->string('session_id')->nullable()->index();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
