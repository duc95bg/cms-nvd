<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Template;
use App\Models\Theme;
use App\Services\BlockRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $site = Site::with('template')
            ->where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        // Blocks-first render: if site has blocks, render from block partials
        if (!empty($site->blocks)) {
            $blocksHtml = BlockRenderer::render($site->blocks);
            return view('site.blocks-page', ['site' => $site, 'blocksHtml' => $blocksHtml]);
        }

        // Fallback: old template-based render
        return view($site->template->view, ['site' => $site]);
    }

    public function index(Request $request): View
    {
        $sites = $request->user()->sites()
            ->with('template')
            ->latest()
            ->paginate(20);

        return view('admin.sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('admin.sites.create', [
            'templates' => Template::orderBy('type')->get(),
            'themes' => Theme::active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'exists:templates,id'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:80', 'unique:sites,slug'],
        ]);

        $siteData = [
            'slug' => $data['slug'],
            'published' => false,
        ];

        // Theme-based: clone blocks_preset
        if (!empty($data['theme_id'])) {
            $theme = Theme::findOrFail($data['theme_id']);
            $siteData['theme_id'] = $theme->id;
            $siteData['blocks'] = $theme->blocks_preset;
            $siteData['template_id'] = Template::first()?->id;
            $siteData['content'] = Template::first()?->default_content ?? [];
        }
        // Template-based (legacy): clone content
        elseif (!empty($data['template_id'])) {
            $template = Template::findOrFail($data['template_id']);
            $siteData['template_id'] = $template->id;
            $siteData['content'] = $template->default_content;
        }

        $site = $request->user()->sites()->create($siteData);

        // Redirect to block editor if theme-based, otherwise to content editor
        if (!empty($data['theme_id'])) {
            return redirect()->route('admin.sites.editor', $site);
        }

        return redirect()->route('admin.sites.edit', $site);
    }

    public function edit(Site $site): View
    {
        $this->authorizeSite($site);

        return view('admin.sites.edit', [
            'site' => $site,
            'locales' => config('app.supported_locales', ['en', 'vi']),
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorizeSite($site);

        $request->validate([
            'content' => ['required', 'array'],
            'published' => ['sometimes', 'boolean'],
        ]);

        $nested = [];
        foreach ($request->input('content', []) as $dotKey => $value) {
            data_set($nested, $dotKey, $this->decodeIfJsonArray($value));
        }

        $site->update([
            'content' => $nested,
            'published' => $request->boolean('published'),
        ]);

        return redirect()
            ->route('admin.sites.edit', $site)
            ->with('status', __('Saved'));
    }

    public function preview(Request $request, Site $site): View
    {
        $this->authorizeSite($site);

        $locale = $request->query('locale');
        if (is_string($locale) && in_array($locale, config('app.supported_locales', ['en', 'vi']), true)) {
            app()->setLocale($locale);
        }

        if (!empty($site->blocks)) {
            $blocksHtml = BlockRenderer::render($site->blocks);
            return view('site.blocks-page', ['site' => $site, 'blocksHtml' => $blocksHtml]);
        }

        return view($site->template->view, ['site' => $site]);
    }

    public function uploadImage(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSite($site);

        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $file = $request->file('image');
        $path = $file->store("sites/{$site->id}", 'public');

        $media = $site->media()->create([
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'id' => $media->id,
            'url' => asset('storage/'.$path),
        ]);
    }

    private function authorizeSite(Site $site): void
    {
        abort_unless($site->user_id === request()->user()?->id, 403);
    }

    /**
     * The edit form renders indexed-list nodes as a JSON textarea.
     * On save, decode strings that parse as arrays so lists survive
     * the round-trip. Non-JSON strings (URLs, plain text) are left alone.
     */
    private function decodeIfJsonArray(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        $trimmed = ltrim($value);
        if ($trimmed === '' || ($trimmed[0] !== '[' && $trimmed[0] !== '{')) {
            return $value;
        }
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return $value;
        }
        return $decoded;
    }
}
