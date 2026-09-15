<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],

            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ai_tools,slug'],

            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],

            'website_url' => ['nullable', 'url', 'max:2048'],
            'affiliate_url' => ['nullable', 'url', 'max:2048'],

            'pricing_type' => ['required', 'in:free,freemium,paid,subscription,contact_sales'],
            'starting_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],

            'features' => ['nullable', 'array'],
            'pros' => ['nullable', 'array'],
            'cons' => ['nullable', 'array'],
            'use_cases' => ['nullable', 'array'],

            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'featured_image' => ['nullable', 'string', 'max:255'],

            'featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
