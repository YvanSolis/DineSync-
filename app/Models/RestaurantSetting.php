<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $fillable = [
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

    public static function current()
    {
        $settings = self::find(1);

        if (!$settings) {
            $settings = self::create([
                'id' => 1,
                'restaurant_name' => 'Chef Oppa',
                'address' => '123 Sample Street, Quezon City, Philippines',
                'contact_number' => '0912 345 6789',
                'opening_days' => 'Monday - Sunday',
                'opening_time' => '10:00 AM',
                'closing_time' => '9:00 PM',
                'reservation_fee' => 300,
                'gcash_name' => 'Chef Oppa',
                'gcash_number' => '0912 345 6789',
                'facebook_url' => null,
                'instagram_url' => null,
                'tiktok_url' => null,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Chef+Oppa',
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.6245772287757!2d121.08242727457433!3d14.677234375196877!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b951b94a41f9%3A0xfb73121b824a2c81!2sChef%20Oppa!5e0!3m2!1sen!2sph!4v1778557370377!5m2!1sen!2sph',
            ]);
        }

        return $settings;
    }
}