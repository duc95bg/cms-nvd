<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::withCount(['values', 'products'])->latest()->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'type' => 'required|in:select,color,text',
            'values' => 'required|array|min:1',
            'values.*.vi' => 'required|string|max:255',
            'values.*.en' => 'required|string|max:255',
        ]);

        $attribute = Attribute::create([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
        ]);

        foreach ($request->input('values', []) as $val) {
            $attribute->values()->create([
                'value' => ['en' => $val['en'], 'vi' => $val['vi']],
                'sort_order' => $val['sort_order'] ?? 0,
            ]);
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', __('Attribute saved.'));
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'type' => 'required|in:select,color,text',
            'values' => 'nullable|array',
            'values.*.vi' => 'required|string|max:255',
            'values.*.en' => 'required|string|max:255',
        ]);

        $attribute->update([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
        ]);

        $existingIds = [];

        foreach ($request->input('values', []) as $val) {
            if (!empty($val['id'])) {
                $attribute->values()->where('id', $val['id'])->update([
                    'value' => ['en' => $val['en'], 'vi' => $val['vi']],
                    'sort_order' => $val['sort_order'] ?? 0,
                ]);
                $existingIds[] = $val['id'];
            } else {
                $created = $attribute->values()->create([
                    'value' => ['en' => $val['en'], 'vi' => $val['vi']],
                    'sort_order' => $val['sort_order'] ?? 0,
                ]);
                $existingIds[] = $created->id;
            }
        }

        $attribute->values()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', __('Attribute saved.'));
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->products()->exists()) {
            return redirect()->route('admin.attributes.index')
                ->with('error', __('Attribute is used by products, cannot delete.'));
        }

        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', __('Attribute deleted.'));
    }
}
