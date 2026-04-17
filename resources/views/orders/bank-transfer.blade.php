@extends('layouts.catalog')

@section('title', __('Bank transfer information') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl border p-8">
            <div class="text-blue-500 mb-4 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-center mb-2">{{ __('Bank transfer information') }}</h1>
            <p class="text-gray-600 text-center mb-8">
                {{ __('Your order number is') }}: <strong class="text-lg">{{ $order->order_number }}</strong>
            </p>

            {{-- Bank details --}}
            <div class="bg-blue-50 rounded-lg p-6 mb-8 space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Bank name') }}</span>
                    <span class="font-semibold">{{ $bankName }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Account number') }}</span>
                    <span class="font-semibold font-mono">{{ $bankAccount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Account holder') }}</span>
                    <span class="font-semibold">{{ $bankHolder }}</span>
                </div>
                <div class="flex justify-between border-t border-blue-200 pt-3">
                    <span class="text-gray-600">{{ __('Transfer reference') }}</span>
                    <span class="font-bold text-blue-700 text-lg">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between border-t border-blue-200 pt-3">
                    <span class="text-gray-600">{{ __('Grand total') }}</span>
                    <span class="font-bold text-blue-700 text-lg">{{ \App\Support\PriceFormatter::format($order->total) }}</span>
                </div>
            </div>

            <p class="text-sm text-gray-500 text-center mb-8">
                {{ __('Please use the order number as your transfer reference so we can identify your payment.') }}
            </p>

            {{-- Items summary --}}
            <div class="border rounded-lg divide-y mb-6">
                @foreach ($order->items as $item)
                    <div class="flex justify-between px-4 py-3">
                        <div>
                            <span class="font-medium">{{ $item->product_name }}</span>
                            <span class="text-gray-500 text-sm">× {{ $item->quantity }}</span>
                        </div>
                        <span class="font-semibold">{{ \App\Support\PriceFormatter::format($item->line_total) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                <a href="/{{ app()->getLocale() }}/products"
                   class="px-6 py-3 border rounded-lg hover:bg-gray-50">{{ __('Continue shopping') }}</a>
            </div>
        </div>
    </div>
@endsection
