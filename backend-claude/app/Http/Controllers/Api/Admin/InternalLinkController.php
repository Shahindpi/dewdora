<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternalLink;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

class InternalLinkController extends Controller
{
    /**
     * Links out of a given post - powers a "related reading" widget.
     */
    public function forPost(int $postId)
    {
        $links = InternalLink::query()
            ->where('source_post_id', $postId)
            ->with('target:id,title,slug,post_type,featured_image')
            ->get();

        return ApiResponse::success($links);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_post_id' => ['required', 'exists:posts,id'],
            'target_post_id' => ['required', 'exists:posts,id', 'different:source_post_id'],
            'anchor_text' => ['required', 'string', 'max:191'],
            'context' => ['nullable', 'string', 'max:500'],
        ]);

        $link = InternalLink::create($validated + ['is_auto_generated' => false]);

        return ApiResponse::success($link, 'Internal link created successfully.', 201);
    }

    public function destroy(InternalLink $internalLink)
    {
        $internalLink->delete();

        return ApiResponse::success(null, 'Internal link removed successfully.');
    }
}
