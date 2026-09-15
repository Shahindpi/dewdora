<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\AffiliateNetwork;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Post;
use App\Models\SeoMeta;

class AffiliateProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'affiliate_network_id',
        'category_id',
        'name',
        'slug',
        'cloaked_slug',
        'short_description',
        'description',
        'website_url',
        'affiliate_url',
        'disclosure_text',
        'price',
        'currency',
        'commission_rate',
        'free_trial',
        'rating',
        'featured_image',
        'pros',
        'cons',
        'featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'rating' => 'decimal:1',

            'free_trial' => 'boolean',
            'featured' => 'boolean',
            'status' => 'boolean',

            'pros' => 'array',
            'cons' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AffiliateProduct $product) {
            // The public API only ever exposes this slug (via redirect_url
            // in AffiliateProductResource) - the raw affiliate_url never
            // leaves the backend except through GoLinkController's redirect.
            if (empty($product->cloaked_slug)) {
                $product->cloaked_slug = Str::slug($product->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function affiliateNetwork(): BelongsTo
    {
        return $this->belongsTo(
            AffiliateNetwork::class
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'post_product'
        )->withPivot([
            'sort_order',
            'is_primary',
        ]);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(
            SeoMeta::class,
            'seoable'
        );
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function registerClick(): void
    {
        $this->increment('click_count');
    }

}