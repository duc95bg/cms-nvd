@extends('layouts.catalog')

@section('title', $currentCategory ? $currentCategory->t('name') . ' — ' . config('app.name') : __('All products') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="/{{ app()->getLocale() }}" class="hover:text-blue-600">{{ __('home') }}</a>
            <span class="mx-1">/</span>
            @if ($currentCategory)
                <a href="/{{ app()->getLocale() }}/products" class="hover:text-blue-600">{{ __('Products') }}</a>
                <span class="mx-1">/</span>
                @if ($currentCategory->parent)
                    <a href="/{{ app()->getLocale() }}/category/{{ $currentCategory->parent->slug }}" class="hover:text-blue-600">
                        {{ $currentCategory->parent->t('name') }}
                    </a>
                    <span class="mx-1">/</span>
                @endif
                <span class="text-gray-900">{{ $currentCategory->t('name') }}</span>
            @else
                <span class="text-gray-900">{{ __('All products') }}</span>
            @endif
        </nav>

        <div class="flex flex-col md:flex-row gap-8">
            {{-- Sidebar: categories --}}
            <aside class="w-full md:w-56 shrink-0">
                <h3 class="font-semibold mb-3">{{ __('Categories') }}</h3>
                <ul class="space-y-1 text-sm">
                    <li>
                        <a href="/{{ app()->getLocale() }}/products"
                           class="block px-3 py-2 rounded-lg {{ !$currentCategory ? 'bg-blue-50 text-blue-700 font-medium' : 'hover:bg-gray-100' }}">
                            {{ __('All products') }}
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="/{{ app()->getLocale() }}/category/{{ $cat->slug }}"
                               class="block px-3 py-2 rounded-lg {{ $currentCategory?->id === $cat->id ? 'bg-blue-50 text-blue-700 font-medium' : 'hover:bg-gray-100' }}">
                                {{ $cat->t('name') }}
                            </a>
                            @if ($cat->children->isNotEmpty())
                                <ul class="ml-4 space-y-1 mt-1">
                                    @foreach ($cat->children as $child)
                                        <li>
                                            <a href="/{{ app()->getLocale() }}/category/{{ $child->slug }}"
                                               class="block px-3 py-1.5 rounded-lg text-gray-600 {{ $currentCategory?->id === $child->id ? 'bg-blue-50 text-blue-700 font-medium' : 'hover:bg-gray-100' }}">
                                                {{ $child->t('name') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- Main content --}}
            <div class="flex-1">
                {{-- Header + sort --}}
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">
                        {{ $currentCategory ? $currentCategory->t('name') : __('All products') }}
                    </h1>
                    <div class="flex items-center gap-2 text-sm">
                        <label>{{ __('Sort by') }}:</label>
                        <select onchange="location.href=this.value" class="border rounded-lg px-3 py-1.5 text-sm">
                            @php
                                $baseUrl = $currentCategory
                                    ? '/' . app()->getLocale() . '/category/' . $currentCategory->slug
                                    : '/' . app()->getLocale() . '/products';
                            @endphp
                            <option value="{{ $baseUrl }}?sort=newest" {{ $sort === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="{{ $baseUrl }}?sort=price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>{{ __('Price: low to high') }}</option>
                            <option value="{{ $baseUrl }}?sort=price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>{{ __('Price: high to low') }}</option>
                            <option value="{{ $baseUrl }}?sort=name" {{ $sort === 'name' ? 'selected' : '' }}>{{ __('Name') }}</option>
                        </select>
                    </div>
                </div>

                @if ($products->isEmpty())
                    <div class="text-center py-16 text-gray-500">
                        <p class="text-lg">{{ __('No products found.') }}</p>
                    </div>
                @else
                    {{-- Product grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($products as $product)
                            @include('products._card', ['product' => $product])
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
