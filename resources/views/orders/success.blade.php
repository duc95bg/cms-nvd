@extends('layouts.catalog')

@section('title', __('Order placed successfully') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-12 text-center">
        <div class="bg-white rounded-xl border p-8">
            <div class="text-green-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-3xl font-bold mb-2">{{ __('Order placed successfully') }}</h1>
            <p class="text-gray-600 mb-6">
                {{ __('Your order number is') }}: <strong class="text-lg">{{ $order->order_number }}</strong>
            </p>

            <div class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 mb-8">
                {{ __('Pending') }}
            </div>

            {{-- Items summary --}}
            <div class="text-left border rounded-lg divide-y mb-6">
                @foreach ($order->items as $item)
                    <div class="flex justify-between px-4 py-3">
                        <div>
                            <span class="font-medium">{{ $item->product_name }}</span>
                            <span class="text-gray-500 text-sm">× {{ $item->quantity }}</span>
                        </div>
                        <span class="font-semibold">{{ \App\Support\PriceFormatter::format($item->line_total) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between px-4 py-3 bg-gray-50 font-bold">
                    <span>{{ __('Grand total') }}</span>
                    <span class="text-blue-600">{{ \App\Support\PriceFormatter::format($order->total) }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/{{ app()->getLocale() }}/products"
                   class="px-6 py-3 border rounded-lg hover:bg-gray-50">{{ __('Continue shopping') }}</a>
                @auth
                    <a href="/{{ app()->getLocale() }}/orders"
                       class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">{{ __('Order history') }}</a>
                @endauth
            </div>
        </div>
    </div>
@endsection
