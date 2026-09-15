<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoMeta;

class AiTool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'post_id',
        'name',
        'slug',
        'cloaked_slug',
        'short_description',
        'description',
        'website_url',
        'affiliate_url',
        'pricing_type',
        'starting_price',
        'currency',
        'features',
        'pros',
        'cons',
        'use_cases',
        'rating',
        'featured_image',
        'featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starting_price' => 'decimal:2',
            'rating' => 'decimal:1',

            'features' => 'array',
            'pros' => 'array',
            'cons' => 'array',
            'use_cases' => 'array',

            'featured' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AiTool $tool) {
            if (empty($tool->cloaked_slug)) {
                $tool->cloaked_slug = Str::slug($tool->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
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
