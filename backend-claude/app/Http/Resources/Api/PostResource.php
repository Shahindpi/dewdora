<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Api\FaqResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'slug' => $this->slug,

            'excerpt' => $this->excerpt,

            'content' => $this->content,

            'featured_image' => $this->featured_image,

            'post_type' => $this->post_type,

            'status' => $this->status,

            'published_at' => $this->published_at,

            'views' => $this->views,

            'reading_time' => $this->reading_time,

            'allow_comments' => $this->allow_comments,

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'category' => CategoryResource::make(
                $this->whenLoaded('category')
            ),

            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),

            'affiliate_products' =>
                AffiliateProductResource::collection(
                    $this->whenLoaded('affiliateProducts')
                ),

            'faqs' => FaqResource::collection(
                $this->whenLoaded('faqs')
            ),

            'seo' => SeoMetaResource::make(
                $this->whenLoaded('seoMeta')
            ),

            // Frontend section routing: /reviews/{slug}, /blog/{slug}, etc.
            'url_path' => $this->urlPath(),
        ];
    }

    private function urlPath(): string
    {
        $prefix = match ($this->post_type?->value ?? $this->post_type) {
            'review' => 'reviews',
            'comparison' => 'comparisons',
            'tutorial' => 'tutorials',
            'deal' => 'deals',
            default => 'blog',
        };

        return "/{$prefix}/{$this->slug}";
    }
}