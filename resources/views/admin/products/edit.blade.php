<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Edit product') }} — {{ $product->t('name', 'vi') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-5xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Edit product') }} — {{ $product->t('name', 'vi') }}</h1>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Main product form --}}
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="bg-white rounded-xl border p-8 space-y-6 mb-8">
            @csrf @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (VI) *</label>
                    <input type="text" name="name[vi]" value="{{ old('name.vi', $product->name['vi'] ?? '') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (EN) *</label>
                    <input type="text" name="name[en]" value="{{ old('name.en', $product->name['en'] ?? '') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Category') }} *</label>
                    <select name="category_id" required class="w-full border rounded-lg px-3 py-2">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->t('name', 'vi') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Base price') }} *</label>
                    <input type="number" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="1000" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Status') }}</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2">
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                <span>{{ __('Featured') }}</span>
            </label>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Short description') }} (VI)</label>
                    <textarea name="short_description[vi]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('short_description.vi', $product->short_description['vi'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Short description') }} (EN)</label>
                    <textarea name="short_description[en]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('short_description.en', $product->short_description['en'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Description') }} (VI)</label>
                    <textarea name="description[vi]" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description.vi', $product->description['vi'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Description') }} (EN)</label>
                    <textarea name="description[en]" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description.en', $product->description['en'] ?? '') }}</textarea>
                </div>
            </div>

            {{-- Attributes --}}
            <div>
                <label class="block font-medium mb-2">{{ __('Select attributes') }}</label>
                <div class="flex flex-wrap gap-3">
                    @foreach ($attributes as $attr)
                        <label class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="attributes[]" value="{{ $attr->id }}"
                                   {{ $product->attributes->contains($attr->id) ? 'checked' : '' }}>
                            <span>{{ $attr->t('name', 'vi') }} ({{ $attr->values->count() }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Inline variants table --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">{{ __('Variants') }}</h2>
                @if ($product->variants->isEmpty())
                    <p class="text-gray-500 text-sm mb-4">{{ __('No variants yet. Select attributes and click Generate variants.') }}</p>
                @else
                    <div class="overflow-x-auto border rounded-lg mb-4">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-100 text-gray-600 uppercase">
                                <tr>
                                    <th class="px-3 py-2">{{ __('SKU') }}</th>
                                    <th class="px-3 py-2">{{ __('Attributes') }}</th>
                                    <th class="px-3 py-2">{{ __('Price') }}</th>
                                    <th class="px-3 py-2">{{ __('Stock') }}</th>
                                    <th class="px-3 py-2">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($product->variants as $i => $variant)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                                            <input type="text" name="variants[{{ $i }}][sku]" value="{{ $variant->sku }}"
                                                   class="border rounded px-2 py-1 w-36 text-xs font-mono">
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-600">
                                            @foreach ($variant->attributeValues as $av)
                                                <span class="inline-block px-2 py-0.5 bg-gray-100 rounded mr-1">{{ $av->value['vi'] ?? $av->value['en'] ?? '' }}</span>
                                            @endforeach
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="variants[{{ $i }}][price]"
                                                   value="{{ $variant->price }}"
                                                   placeholder="{{ $product->base_price }}"
                                                   step="1000" min="0"
                                                   class="border rounded px-2 py-1 w-28">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="variants[{{ $i }}][stock]"
                                                   value="{{ $variant->stock }}" min="0"
                                                   class="border rounded px-2 py-1 w-20">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select name="variants[{{ $i }}][status]" class="border rounded px-2 py-1 text-xs">
                                                <option value="active" {{ $variant->status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                                <option value="inactive" {{ $variant->status === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">{{ __('Save') }}</button>
        </form>

        {{-- Generate variants (separate form) --}}
        <form method="POST" action="{{ route('admin.products.variants.generate', $product) }}" class="mb-8">
            @csrf
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                {{ __('Generate variants') }}
            </button>
        </form>

        {{-- Images section --}}
        <div class="bg-white rounded-xl border p-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('Images') }}</h2>

            @if ($product->images->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    @foreach ($product->images as $img)
                        <div class="relative border rounded-lg p-2 {{ $img->is_primary ? 'ring-2 ring-blue-500' : '' }}">
                            <img src="{{ $img->url }}" alt="" class="w-full h-32 object-cover rounded">
                            @if ($img->is_primary)
                                <span class="absolute top-1 left-1 px-2 py-0.5 bg-blue-600 text-white text-xs rounded">Primary</span>
                            @endif
                            <div class="flex gap-1 mt-2">
                                @unless ($img->is_primary)
                                    <form method="POST" action="{{ route('admin.products.images.primary', [$product, $img]) }}">
                                        @csrf
                                        <button class="px-2 py-1 border rounded text-xs hover:bg-gray-50">{{ __('Set as primary') }}</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}"
                                      onsubmit="return confirm('{{ __('Delete') }}?')">
                                    @csrf @method('DELETE')
                                    <button class="px-2 py-1 border border-red-300 text-red-600 rounded text-xs hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form id="image-upload-form" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="image" accept="image/*" required class="border rounded-lg px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">{{ __('Upload images') }}</button>
            </form>
            <div id="upload-result" class="mt-2 text-sm"></div>
        </div>
    </div>

    <script>
        document.getElementById('image-upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const resultEl = document.getElementById('upload-result');
            resultEl.textContent = 'Uploading...';

            try {
                const res = await fetch('{{ route("admin.products.images.upload", $product) }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!res.ok) throw new Error('Upload failed');
                const data = await res.json();
                resultEl.innerHTML = '<span class="text-green-600">Uploaded!</span>';
                setTimeout(() => location.reload(), 500);
            } catch (err) {
                resultEl.innerHTML = '<span class="text-red-600">' + err.message + '</span>';
            }
        });
    </script>
</body>
</html>
