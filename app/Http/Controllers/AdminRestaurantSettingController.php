<?php

namespace App\Http\Controllers;

use App\Models\RestaurantSetting;
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
        $request->validate([
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

        $settings->update([
            'restaurant_name' => $request->restaurant_name,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'opening_days' => $request->opening_days,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'reservation_fee' => $request->reservation_fee,
            'gcash_name' => $request->gcash_name,
            'gcash_number' => $request->gcash_number,
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'tiktok_url' => $request->tiktok_url,
            'map_embed_url' => $request->map_embed_url,
            'google_maps_url' => $request->google_maps_url,
        ]);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Restaurant settings updated successfully.');
    }
}