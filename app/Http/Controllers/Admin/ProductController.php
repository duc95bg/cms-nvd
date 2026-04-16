<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->withCount('variants')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        $attributes = Attribute::with('values')->get();

        return view('admin.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,draft,inactive',
            'featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
            'description.vi' => 'nullable|string',
            'description.en' => 'nullable|string',
            'short_description.vi' => 'nullable|string',
            'short_description.en' => 'nullable|string',
            'attributes' => 'nullable|array',
        ]);

        $data = $request->only(['name', 'category_id', 'base_price', 'status', 'sort_order', 'description', 'short_description']);
        $data['slug'] = $request->input('slug') ?: Str::slug($request->input('name.vi'));
        $data['featured'] = $request->boolean('featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $product = Product::create($data);
        $product->attributes()->sync($request->input('attributes', []));

        return redirect()->route('admin.products.edit', $product)
            ->with('success', __('Product saved.'));
    }

    public function edit(Product $product): View
    {
        $product->load(['attributes.values', 'variants.attributeValues', 'images']);
        $categories = Category::active()->orderBy('sort_order')->get();
        $attributes = Attribute::with('values')->get();

        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name.vi' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,draft,inactive',
            'featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
            'description.vi' => 'nullable|string',
            'description.en' => 'nullable|string',
            'short_description.vi' => 'nullable|string',
            'short_description.en' => 'nullable|string',
            'attributes' => 'nullable|array',
        ]);

        $data = $request->only(['name', 'category_id', 'base_price', 'status', 'sort_order', 'description', 'short_description']);
        $data['slug'] = $request->input('slug') ?: Str::slug($request->input('name.vi'));
        $data['featured'] = $request->boolean('featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $product->update($data);
        $product->attributes()->sync($request->input('attributes', []));

        // Update inline variants
        foreach ($request->input('variants', []) as $variantData) {
            if (!empty($variantData['id'])) {
                ProductVariant::where('id', $variantData['id'])
                    ->where('product_id', $product->id)
                    ->update([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'] !== '' ? $variantData['price'] : null,
                        'stock' => (int) ($variantData['stock'] ?? 0),
                        'status' => $variantData['status'] ?? 'active',
                    ]);
            }
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', __('Product saved.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Delete image files
        foreach ($product->images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        }

        $product->delete(); // cascades to variants, images, pivots

        return redirect()->route('admin.products.index')
            ->with('success', __('Product deleted.'));
    }

    public function generateVariants(Product $product): RedirectResponse
    {
        $attributeGroups = $product->attributes()->with('values')->get()
            ->pluck('values')
            ->toArray();

        if (empty($attributeGroups)) {
            return redirect()->route('admin.products.edit', $product)
                ->with('error', __('Select attributes first.'));
        }

        // Cartesian product
        $combos = [[]];
        foreach ($attributeGroups as $values) {
            $new = [];
            foreach ($combos as $combo) {
                foreach ($values as $value) {
                    $new[] = array_merge($combo, [$value]);
                }
            }
            $combos = $new;
        }

        $created = 0;
        foreach ($combos as $combo) {
            $valueIds = array_column($combo, 'id');
            sort($valueIds);

            // Check if variant with this exact value set exists
            $exists = $product->variants()->get()->first(function ($variant) use ($valueIds) {
                $existing = $variant->attributeValues()->pluck('attribute_values.id')->sort()->values()->all();
                return $existing === $valueIds;
            });

            if ($exists) {
                continue;
            }

            $skuParts = array_map(function ($v) {
                return strtoupper(Str::slug($v['value']['en'] ?? $v['value']['vi'] ?? 'X', '-'));
            }, $combo);

            $sku = strtoupper(Str::slug($product->slug)) . '-' . implode('-', $skuParts);

            // Ensure SKU uniqueness
            $baseSku = $sku;
            $counter = 1;
            while (ProductVariant::where('sku', $sku)->exists()) {
                $sku = $baseSku . '-' . $counter++;
            }

            $variant = $product->variants()->create([
                'sku' => $sku,
                'price' => null,
                'stock' => 0,
                'status' => 'active',
            ]);

            $variant->attributeValues()->sync($valueIds);
            $created++;
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', __('Variants generated.') . " ($created)");
    }

    public function uploadImage(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        if ($product->images()->count() >= 10) {
            return response()->json(['error' => 'Max 10 images'], 422);
        }

        $path = $request->file('image')->store("products/{$product->id}", 'public');
        $isPrimary = !$product->images()->where('is_primary', true)->exists();

        $image = $product->images()->create([
            'path' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $product->images()->max('sort_order') + 1,
        ]);

        return response()->json([
            'id' => $image->id,
            'url' => $image->url,
        ]);
    }

    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        $image->delete();

        return redirect()->route('admin.products.edit', $product)
            ->with('success', __('Image deleted.'));
    }

    public function setPrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return redirect()->route('admin.products.edit', $product);
    }
}
