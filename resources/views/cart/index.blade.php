@extends('layouts.catalog')

@section('title', __('Shopping cart') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Shopping cart') }}</h1>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="text-center py-16 bg-white rounded-xl border">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <p class="text-lg text-gray-500 mb-4">{{ __('Your cart is empty') }}</p>
                <a href="/{{ app()->getLocale() }}/products" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('Start shopping') }}
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="px-4 py-3" colspan="2">{{ __('Product') }}</th>
                            <th class="px-4 py-3">{{ __('Unit price') }}</th>
                            <th class="px-4 py-3">{{ __('Quantity') }}</th>
                            <th class="px-4 py-3">{{ __('Line total') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 w-16">
                                    @if ($item->product->primaryImage())
                                        <img src="{{ $item->product->primaryImage()->url }}" alt=""
                                             class="w-14 h-14 object-cover rounded">
                                    @else
                                        <div class="w-14 h-14 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">—</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $item->product->t('name') }}</div>
                                    @if ($item->variant)
                                        <div class="text-xs text-gray-500 mt-1">
                                            @foreach ($item->variant->attributeValues as $av)
                                                <span class="inline-block px-2 py-0.5 bg-gray-100 rounded mr-1">
                                                    {{ $av->attribute->t('name') }}: {{ $av->t('value') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ \App\Support\PriceFormatter::format($item->unit_price) }}
                                </td>
                                <td class="px-4 py-3">
                                    <form action="/cart/update" method="POST" class="flex items-center gap-1">
                                        @csrf
                                        <input type="hidden" name="key" value="{{ $item->key }}">
                                        <input type="number" name="qty" value="{{ $item->quantity }}" min="0"
                                               class="border rounded px-2 py-1 w-16 text-center text-sm">
                                        <button type="submit" class="px-2 py-1 border rounded text-xs hover:bg-gray-100">{{ __('Update') }}</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 font-semibold text-sm">
                                    {{ \App\Support\PriceFormatter::format($item->line_total) }}
                                </td>
                                <td class="px-4 py-3">
                                    <form action="/cart/remove" method="POST">
                                        @csrf
                                        <input type="hidden" name="key" value="{{ $item->key }}">
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-lg" title="{{ __('Remove') }}">✕</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <a href="/{{ app()->getLocale() }}/products" class="text-blue-600 hover:underline">
                    ← {{ __('Continue shopping') }}
                </a>
                <div class="text-right">
                    <div class="text-lg mb-3">
                        {{ __('Grand total') }}:
                        <span class="text-2xl font-bold text-blue-600">{{ \App\Support\PriceFormatter::format($total) }}</span>
                    </div>
                    <a href="/{{ app()->getLocale() }}/checkout"
                       class="inline-block px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        {{ __('Checkout') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
