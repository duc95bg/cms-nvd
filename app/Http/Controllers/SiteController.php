<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Template;
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'exists:templates,id'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:80', 'unique:sites,slug'],
        ]);

        $template = Template::findOrFail($data['template_id']);

        $site = $request->user()->sites()->create([
            'template_id' => $template->id,
            'slug' => $data['slug'],
            'content' => $template->default_content,
            'published' => false,
        ]);

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
            data_set($nested, $dotKey, $value);
        }

        $site->update([
            'content' => $nested,
            'published' => $request->boolean('published'),
        ]);

        return redirect()
            ->route('admin.sites.edit', $site)
            ->with('status', __('Saved'));
    }

    public function preview(Site $site): View
    {
        $this->authorizeSite($site);

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
}
