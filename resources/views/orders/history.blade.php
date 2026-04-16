@extends('layouts.catalog')

@section('title', __('Order history') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Order history') }}</h1>

        @php
            $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'confirmed' => 'bg-blue-100 text-blue-800',
                'shipping' => 'bg-orange-100 text-orange-800',
                'delivered' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800',
            ];
        @endphp

        @if ($orders->isEmpty())
            <div class="text-center py-16 bg-white rounded-xl border">
                <p class="text-lg text-gray-500 mb-4">{{ __('No orders yet') }}</p>
                <a href="/{{ app()->getLocale() }}/products"
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('Start shopping') }}
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <a href="/{{ app()->getLocale() }}/orders/{{ $order->id }}"
                       class="block bg-white rounded-xl border p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-sm text-gray-600">{{ $order->order_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100' }}">
                                {{ __(ucfirst($order->status)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            <span class="font-bold text-blue-600">{{ \App\Support\PriceFormatter::format($order->total) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
