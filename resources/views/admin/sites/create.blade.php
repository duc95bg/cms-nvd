<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Create site') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="bg-white shadow rounded-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Create a new site') }}</h1>
            <p class="text-sm text-gray-600 mb-6">{{ __('Create site') }}</p>

            <form method="POST" action="{{ route('admin.sites.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Template') }}</label>
                    <select
                        id="template_id"
                        name="template_id"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                    >
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->type }})</option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Slug') }}</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        required
                        maxlength="80"
                        pattern="[a-z0-9\-]+"
                        value="{{ old('slug') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                    >
                    <p class="mt-1 text-xs text-gray-500">{{ __('Lowercase letters, numbers and dashes only.') }}</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ __('Create site') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
