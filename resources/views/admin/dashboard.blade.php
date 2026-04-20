<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Dashboard') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')

    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Dashboard') }}</h1>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                <div class="text-2xl mb-1">💰</div>
                <div class="text-sm text-green-700">{{ __('Total revenue') }}</div>
                <div class="text-2xl font-bold text-green-800">{{ \App\Support\PriceFormatter::format($stats['total_revenue']) }}</div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                <div class="text-2xl mb-1">📦</div>
                <div class="text-sm text-blue-700">{{ __('Total orders') }}</div>
                <div class="text-2xl font-bold text-blue-800">{{ number_format($stats['total_orders']) }}</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
                <div class="text-2xl mb-1">📅</div>
                <div class="text-sm text-yellow-700">{{ __('Today orders') }}</div>
                <div class="text-2xl font-bold text-yellow-800">{{ $stats['today_orders'] }}</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                <div class="text-2xl mb-1">⏳</div>
                <div class="text-sm text-red-700">{{ __('Pending orders') }}</div>
                <div class="text-2xl font-bold text-red-800">{{ $stats['pending_orders'] }}</div>
            </div>
        </div>

        {{-- Revenue chart --}}
        <div class="bg-white rounded-xl border p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('Revenue last 7 days') }}</h2>
            @php $maxRevenue = max(array_column($chart, 'total')) ?: 1; @endphp
            <div class="space-y-3">
                @foreach ($chart as $day)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500 w-12 text-right">{{ $day['label'] }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all"
                                 style="width: {{ ($day['total'] / $maxRevenue) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium w-28 text-right">{{ \App\Support\PriceFormatter::format($day['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2-column: top products + recent orders --}}
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Top products --}}
            <div class="bg-white rounded-xl border p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('Top selling products') }}</h2>
                @if ($topProducts->isEmpty())
                    <p class="text-gray-500 text-sm">{{ __('No data') }}</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-gray-500 text-xs uppercase border-b">
                            <tr>
                                <th class="pb-2 text-left">#</th>
                                <th class="pb-2 text-left">{{ __('Product') }}</th>
                                <th class="pb-2 text-right">{{ __('Quantity sold') }}</th>
                                <th class="pb-2 text-right">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($topProducts as $i => $tp)
                                <tr>
                                    <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="py-2 font-medium">{{ $tp->product_name }}</td>
                                    <td class="py-2 text-right">{{ $tp->total_qty }}</td>
                                    <td class="py-2 text-right">{{ \App\Support\PriceFormatter::format($tp->total_revenue) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Recent orders --}}
            <div class="bg-white rounded-xl border p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('Recent orders') }}</h2>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'shipping' => 'bg-orange-100 text-orange-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                @endphp
                @if ($recentOrders->isEmpty())
                    <p class="text-gray-500 text-sm">{{ __('No data') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentOrders as $order)
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition">
                                <div>
                                    <span class="font-mono text-xs text-gray-500">{{ $order->order_number }}</span>
                                    <div class="text-sm font-medium">{{ $order->customer_name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-sm">{{ \App\Support\PriceFormatter::format($order->total) }}</div>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100' }}">
                                        {{ __(ucfirst($order->status)) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
