<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Products') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Products') }}</h1>
            <a href="{{ route('admin.products.create') }}"
               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                {{ __('Add product') }}
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Category') }}</th>
                        <th class="px-4 py-3">{{ __('Base price') }}</th>
                        <th class="px-4 py-3">{{ __('Variants') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Featured') }}</th>
                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $product->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $product->t('name', 'vi') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $product->category?->t('name', 'vi') }}</td>
                            <td class="px-4 py-3 text-sm">{{ \App\Support\PriceFormatter::format($product->base_price) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $product->variants_count }}</td>
                            <td class="px-4 py-3">
                                @if ($product->status === 'active')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">{{ __('Active') }}</span>
                                @elseif ($product->status === 'draft')
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">{{ __('Draft') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $product->featured ? '✓' : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
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

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</body>
</html>
