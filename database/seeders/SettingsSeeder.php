<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => ['vi' => 'CMS NVD', 'en' => 'CMS NVD'],
            'tagline' => ['vi' => 'Hệ thống quản lý nội dung', 'en' => 'Content Management System'],
            'logo' => '',
            'favicon' => '',
            'email' => 'info@example.com',
            'phone' => '+84 123 456 789',
            'address' => ['vi' => 'TP. Hồ Chí Minh, Việt Nam', 'en' => 'Ho Chi Minh City, Vietnam'],
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'social_tiktok' => '',
        ];

        foreach ($defaults as $key => $value) {
            // Only set if not already present
            if (SettingService::get($key) === null) {
                SettingService::set($key, $value);
            }
        }

        SettingService::flush();
    }
}
