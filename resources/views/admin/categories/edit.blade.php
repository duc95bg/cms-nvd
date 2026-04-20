<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Edit category') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-3xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Edit category') }} — {{ $category->t('name', 'vi') }}</h1>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data"
              class="bg-white rounded-xl border p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (VI) *</label>
                    <input type="text" name="name[vi]" value="{{ old('name.vi', $category->name['vi'] ?? '') }}" required
                           class="w-full border rounded-lg px-3 py-2">
                    @error('name.vi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (EN) *</label>
                    <input type="text" name="name[en]" value="{{ old('name.en', $category->name['en'] ?? '') }}" required
                           class="w-full border rounded-lg px-3 py-2">
                    @error('name.en')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">{{ __('Slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="auto-generate"
                       class="w-full border rounded-lg px-3 py-2">
                @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block font-medium mb-1">{{ __('Parent category') }}</label>
                <select name="parent_id" class="w-full border rounded-lg px-3 py-2">
                    <option value="">{{ __('No parent (top level)') }}</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->t('name', 'vi') }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block font-medium mb-1">{{ __('Image') }}</label>
                @if ($category->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="" class="h-24 rounded-lg border">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full border rounded-lg px-3 py-2">
                @error('image')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Status') }}</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2">
                        <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                {{ __('Save') }}
            </button>
        </form>
    </div>
</body>
</html>
