<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoMetaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            'canonical_url' => $this->canonical_url,

            'og_title' => $this->og_title,
            'og_description' => $this->og_description,
            'og_image' => $this->og_image,

            'twitter_title' => $this->twitter_title,
            'twitter_description' => $this->twitter_description,
            'twitter_image' => $this->twitter_image,

            'robots' => $this->robots,

            'schema_data' => $this->schema_data,
        ];
    }
}