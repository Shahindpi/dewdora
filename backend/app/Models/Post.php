<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'post_type',
        'status',
        'published_at',
        'views',
        'reading_time',
        'allow_comments',
    ];

    protected function casts(): array
    {
        return [
            'post_type' => PostType::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'views' => 'integer',
            'reading_time' => 'integer',
            'allow_comments' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function affiliateProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            AffiliateProduct::class,
            'post_product'
        )->withPivot([
            'sort_order',
            'is_primary',
        ])->orderByPivot('sort_order');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(
            SeoMeta::class,
            'seoable'
        );
    }

}