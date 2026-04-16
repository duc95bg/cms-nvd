<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(string $locale, Request $request): View
    {
        $query = Product::active()
            ->with(['category', 'variants' => fn ($q) => $q->active(), 'images']);

        $query = $this->applySort($query, $request->input('sort'));

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->topLevel()->with('children')->orderBy('sort_order')->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => null,
            'sort' => $request->input('sort', 'newest'),
        ]);
    }

    public function byCategory(string $locale, string $slug): View
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();

        $categoryIds = collect([$category->id])
            ->merge($category->children()->pluck('id'));

        $query = Product::active()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'variants' => fn ($q) => $q->active(), 'images']);

        $query = $this->applySort($query, request()->input('sort'));

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->topLevel()->with('children')->orderBy('sort_order')->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category,
            'sort' => request()->input('sort', 'newest'),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with([
                'category.parent',
                'images',
                'attributes.values',
                'variants' => fn ($q) => $q->active()->with('attributeValues'),
            ])
            ->firstOrFail();

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images', 'variants' => fn ($q) => $q->active()])
            ->limit(4)
            ->inRandomOrder()
            ->get();

        return view('products.show', compact('product', 'related'));
    }

    private function applySort($query, ?string $sort)
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'name' => $query->orderByRaw("JSON_EXTRACT(name, '$.\"" . app()->getLocale() . "\"') ASC"),
            default => $query->latest(),
        };
    }
}
