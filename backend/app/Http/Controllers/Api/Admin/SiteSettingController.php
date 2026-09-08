<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\UpdateSiteSettingRequest;
use App\Http\Resources\SiteSettingResource;

use App\Models\SiteSetting;

use App\Services\CacheService;
use App\Support\ApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingController extends Controller
{
    /**
     * Singleton settings record.
     */
    protected function settings(): SiteSetting
    {
        return SiteSetting::firstOrCreate([
            'id' => 1,
        ]);
    }

    /**
     * GET /admin/settings
     */
    public function show(): JsonResponse
    {
        return ApiResponse::success(
            SiteSettingResource::make($this->settings()),
            'Site settings retrieved successfully.'
        );
    }

    /**
     * PUT /admin/settings
     */
    public function update(
        UpdateSiteSettingRequest $request
    ): JsonResponse {

        $settings = $this->settings();

        $settings->update($request->validated());

        CacheService::clearSiteSettings();

        return ApiResponse::success(
            SiteSettingResource::make($settings->refresh()),
            'Site settings updated successfully.'
        );
    }

    /**
     * POST /admin/settings/assets
     */
    public function uploadAssets(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico,webp|max:1024',
        ]);

        $settings = $this->settings();

        foreach (['logo', 'favicon'] as $field) {

            if (! $request->hasFile($field)) {
                continue;
            }

            if (
                $settings->{$field} &&
                Storage::disk('public')->exists($settings->{$field})
            ) {
                Storage::disk('public')->delete($settings->{$field});
            }

            $file = $request->file($field);

            $path = $file->storeAs(
                'uploads/settings',
                Str::uuid() . '.' . $file->extension(),
                'public'
            );

            $settings->{$field} = $path;
        }

        $settings->save();

        CacheService::clearSiteSettings();

        return ApiResponse::success(
            SiteSettingResource::make($settings->refresh()),
            'Site assets updated successfully.'
        );
    }
}