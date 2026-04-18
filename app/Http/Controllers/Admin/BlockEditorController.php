<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlockEditorController extends Controller
{
    public function edit(Site $site): View
    {
        $this->authorizeSite($site);

        return view('admin.sites.editor', [
            'site' => $site,
            'blocksJson' => json_encode($site->blocks ?? [], JSON_UNESCAPED_UNICODE),
            'blockTypes' => config('blocks'),
            'blockTypesJson' => json_encode(config('blocks'), JSON_UNESCAPED_UNICODE),
            'categories' => Category::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSite($site);

        $request->validate([
            'blocks' => 'present|array',
        ]);

        $site->update(['blocks' => $request->input('blocks')]);

        return response()->json(['success' => true]);
    }

    public function uploadImage(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSite($site);

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store("sites/{$site->id}", 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    private function authorizeSite(Site $site): void
    {
        abort_unless($site->user_id === request()->user()?->id, 403);
    }
}
