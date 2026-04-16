<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Add product') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Add product') }}</h1>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" class="bg-white rounded-xl border p-8 space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (VI) *</label>
                    <input type="text" name="name[vi]" value="{{ old('name.vi') }}" required class="w-full border rounded-lg px-3 py-2">
                    @error('name.vi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (EN) *</label>
                    <input type="text" name="name[en]" value="{{ old('name.en') }}" required class="w-full border rounded-lg px-3 py-2">
                    @error('name.en')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generate" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Category') }} *</label>
                    <select name="category_id" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">{{ __('Select category') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->t('name', 'vi') }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Base price') }} *</label>
                    <input type="number" name="base_price" value="{{ old('base_price', 0) }}" min="0" step="1000" required class="w-full border rounded-lg px-3 py-2">
                    @error('base_price')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Status') }}</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2">
                        <option value="draft">{{ __('Draft') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }}>
                <span>{{ __('Featured') }}</span>
            </label>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Short description') }} (VI)</label>
                    <textarea name="short_description[vi]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('short_description.vi') }}</textarea>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Short description') }} (EN)</label>
                    <textarea name="short_description[en]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('short_description.en') }}</textarea>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Description') }} (VI)</label>
                    <textarea name="description[vi]" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description.vi') }}</textarea>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Description') }} (EN)</label>
                    <textarea name="description[en]" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description.en') }}</textarea>
                </div>
            </div>

            <div>
                <label class="block font-medium mb-2">{{ __('Select attributes') }}</label>
                <div class="flex flex-wrap gap-3">
                    @foreach ($attributes as $attr)
                        <label class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="attributes[]" value="{{ $attr->id }}"
                                   {{ in_array($attr->id, old('attributes', [])) ? 'checked' : '' }}>
                            <span>{{ $attr->t('name', 'vi') }} ({{ $attr->values->count() }} {{ __('Values') }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">{{ __('Save') }}</button>
        </form>
    </div>
</body>
</html>
