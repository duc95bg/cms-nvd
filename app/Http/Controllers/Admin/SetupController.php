<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Theme;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetupController extends Controller
{
    /**
     * Check if site is already set up.
     */
    public static function isSetUp(): bool
    {
        return Site::where('slug', 'homepage')->exists();
    }

    /**
     * Get the main site (homepage).
     */
    public static function getMainSite(): ?Site
    {
        return Site::where('slug', 'homepage')->first();
    }

    public function index(): View|RedirectResponse
    {
        if (self::isSetUp()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.setup', [
            'themes' => Theme::active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (self::isSetUp()) {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'site_name_vi' => 'required|string|max:255',
            'site_name_en' => 'required|string|max:255',
        ]);

        $theme = Theme::findOrFail($request->theme_id);

        // Create the single homepage site
        Site::create([
            'user_id' => $request->user()->id,
            'template_id' => \App\Models\Template::first()?->id,
            'theme_id' => $theme->id,
            'slug' => 'homepage',
            'content' => \App\Models\Template::first()?->default_content ?? [],
            'blocks' => $theme->blocks_preset,
            'published' => true,
        ]);

        // Save site name to settings
        SettingService::set('site_name', [
            'vi' => $request->site_name_vi,
            'en' => $request->site_name_en,
        ]);

        SettingService::flush();

        return redirect()->route('admin.dashboard')
            ->with('success', __('Site setup complete!'));
    }
}
