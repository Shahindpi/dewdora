<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_products', function (Blueprint $table) {
            // Public-facing redirect path segment, e.g. /go/{cloaked_slug}.
            // AffiliateProductResource now returns this instead of the raw
            // affiliate_url - see GoLinkController for the redirect itself.
            $table->string('cloaked_slug', 220)->nullable()->unique()->after('slug');

            // Short FTC/affiliate-program disclosure shown near the CTA
            // ("We may earn a commission...").
            $table->string('disclosure_text')->nullable()->after('affiliate_url');

            $table->unsignedBigInteger('click_count')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->dropColumn(['cloaked_slug', 'disclosure_text', 'click_count']);
        });
    }
};
