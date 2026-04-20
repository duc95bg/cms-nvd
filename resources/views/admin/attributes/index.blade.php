<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Attributes') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-5xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Attributes') }}</h1>
            <a href="{{ route('admin.attributes.create') }}"
               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                {{ __('Add attribute') }}
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Type') }}</th>
                        <th class="px-4 py-3">{{ __('Values') }}</th>
                        <th class="px-4 py-3">{{ __('Products') }}</th>
                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($attributes as $attribute)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">{{ $attribute->t('name', 'vi') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $attribute->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $attribute->values_count }}</td>
                            <td class="px-4 py-3 text-sm">{{ $attribute->products_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.attributes.edit', $attribute) }}"
                                       class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}"
                                          onsubmit="return confirm('{{ __('Delete') }}?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1 border border-red-300 text-red-600 rounded hover:bg-red-50 text-sm">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
