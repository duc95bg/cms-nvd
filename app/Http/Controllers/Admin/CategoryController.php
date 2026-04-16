<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount(['children', 'products'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::topLevel()->orderBy('sort_order')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:5120',
            'sort_order' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $validated;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']['vi'] ?? $data['name']['en']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', __('Category saved.'));
    }

    public function edit(Category $category)
    {
        $childrenIds = $category->children()->pluck('id')->toArray();

        $parents = Category::topLevel()
            ->where('id', '!=', $category->id)
            ->whereNotIn('id', $childrenIds)
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:5120',
            'sort_order' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $validated;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']['vi'] ?? $data['name']['en']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', __('Category saved.'));
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', __('Category has products cannot delete.'));
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', __('Category deleted.'));
    }
}
