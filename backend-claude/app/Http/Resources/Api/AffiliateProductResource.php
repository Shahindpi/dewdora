<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\FaqResource;
use App\Http\Resources\Api\PostResource;
use App\Http\Resources\Api\SeoMetaResource;

class AffiliateProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'short_description' => $this->short_description,

            'description' => $this->description,

            'website_url' => $this->website_url,

            // Never expose the raw affiliate_url to the public API - the
            // frontend CTA should always point at this cloaked redirect
            // path, resolved server-side by GoLinkController.
            'redirect_url' => "/go/{$this->cloaked_slug}",

            'disclosure_text' => $this->disclosure_text,

            'price' => $this->price,

            'currency' => $this->currency,

            'free_trial' => $this->free_trial,

            'rating' => $this->rating,

            'featured_image' => $this->featured_image,

            'pros' => $this->pros,

            'cons' => $this->cons,

            'featured' => $this->featured,

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'brand' => BrandResource::make(
                $this->whenLoaded('brand')
            ),

            'category' => CategoryResource::make(
                $this->whenLoaded('category')
            ),

            'seo' => SeoMetaResource::make(
                $this->whenLoaded('seoMeta')
            ),

            'faqs' => FaqResource::collection(
                $this->whenLoaded('faqs')
            ),

            // Only surfaced to authenticated admin users editing the record
            // in the CMS - never part of the public storefront payload.
            'affiliate_url' => $this->when(
                $request->user()?->role?->slug === 'admin',
                $this->affiliate_url
            ),

        ];
    }
}