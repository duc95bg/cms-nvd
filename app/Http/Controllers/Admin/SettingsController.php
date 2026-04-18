<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SettingService::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = ['site_name', 'tagline', 'email', 'phone', 'address',
                    'social_facebook', 'social_instagram', 'social_youtube', 'social_tiktok'];

        foreach ($fields as $key) {
            if ($request->has($key)) {
                SettingService::set($key, $request->input($key));
            }
        }

        // Logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            SettingService::set('logo', asset('storage/' . $path));
        }

        // Favicon upload
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            SettingService::set('favicon', asset('storage/' . $path));
        }

        SettingService::flush();

        return redirect()->route('admin.settings.edit')
            ->with('success', __('Settings saved'));
    }
}
