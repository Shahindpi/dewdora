<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\FaqResource;
use App\Http\Resources\Api\SeoMetaResource;

class AiToolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'short_description' => $this->short_description,

            'description' => $this->description,

            'website_url' => $this->website_url,

            // Never expose the raw affiliate_url to the public API - see
            // GoLinkController for the redirect this resolves to.
            'redirect_url' => "/go/{$this->cloaked_slug}",

            'pricing_type' => $this->pricing_type,

            'starting_price' => $this->starting_price,

            'currency' => $this->currency,

            'features' => $this->features,

            'pros' => $this->pros,

            'cons' => $this->cons,

            'use_cases' => $this->use_cases,

            'rating' => $this->rating,

            'featured_image' => $this->featured_image,

            'featured' => $this->featured,

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'category' => CategoryResource::make(
                $this->whenLoaded('category')
            ),

            'seo' => SeoMetaResource::make(
                $this->whenLoaded('seoMeta')
            ),

            'faqs' => FaqResource::collection(
                $this->whenLoaded('faqs')
            ),

            'affiliate_url' => $this->when(
                $request->user()?->role?->slug === 'admin',
                $this->affiliate_url
            ),

        ];
    }
}
