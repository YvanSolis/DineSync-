<?php

namespace App\Http\Controllers;

use App\Models\RestaurantSetting;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminRestaurantSettingController extends Controller
{
    public function edit()
    {
        $settings = RestaurantSetting::current();

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'opening_days' => ['required', 'string', 'max:255'],
            'opening_time' => ['required', 'string', 'max:50'],
            'closing_time' => ['required', 'string', 'max:50'],
            'reservation_fee' => ['required', 'numeric', 'min:0'],
            'gcash_name' => ['nullable', 'string', 'max:255'],
            'gcash_number' => ['nullable', 'string', 'max:50'],
            'facebook_url' => ['nullable', 'string', 'max:500'],
            'instagram_url' => ['nullable', 'string', 'max:500'],
            'tiktok_url' => ['nullable', 'string', 'max:500'],
            'map_embed_url' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'string'],
        ]);

        $settings = RestaurantSetting::current();

        $trackedFields = [
            'restaurant_name',
            'address',
            'contact_number',
            'opening_days',
            'opening_time',
            'closing_time',
            'reservation_fee',
            'gcash_name',
            'gcash_number',
            'facebook_url',
            'instagram_url',
            'tiktok_url',
            'map_embed_url',
            'google_maps_url',
        ];

        $oldValues = $settings->only($trackedFields);

        $settings->update($validated);
        $settings->refresh();

        AuditService::record(
            module: 'Settings',
            action: 'update',
            description: 'Updated restaurant settings.',
            auditable: $settings,
            oldValues: $oldValues,
            newValues: $settings->only($trackedFields),
            request: $request
        );

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Restaurant settings updated successfully.');
    }
}
