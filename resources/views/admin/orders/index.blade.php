<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Orders') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-7xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">{{ __('Orders') }}</h1>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 mb-6 bg-white p-4 rounded-xl border">
            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" {{ $currentStatus === $s ? 'selected' : '' }}>{{ __(ucfirst($s)) }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="{{ __('Search by order #, name, email...') }}"
                   class="border rounded-lg px-3 py-2 text-sm flex-1 min-w-[200px]">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">{{ __('Filter') }}</button>
            @if ($currentStatus || $search)
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">{{ __('Clear') }}</a>
            @endif
        </form>

        @php
            $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'confirmed' => 'bg-blue-100 text-blue-800',
                'shipping' => 'bg-orange-100 text-orange-800',
                'delivered' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800',
            ];
            $paymentColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'paid' => 'bg-green-100 text-green-800',
                'failed' => 'bg-red-100 text-red-800',
                'refunded' => 'bg-gray-100 text-gray-800',
            ];
        @endphp

        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">{{ __('Order number') }}</th>
                        <th class="px-4 py-3">{{ __('Customer') }}</th>
                        <th class="px-4 py-3">{{ __('Payment') }}</th>
                        <th class="px-4 py-3">{{ __('Payment status') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Grand total') }}</th>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline font-mono text-xs">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $order->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-gray-100 rounded text-xs uppercase">{{ $order->payment_method }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $paymentColors[$order->payment_status] ?? 'bg-gray-100' }}">
                                    {{ __(ucfirst($order->payment_status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100' }}">
                                    {{ __(ucfirst($order->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                {{ \App\Support\PriceFormatter::format($order->total) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No orders yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</body>
</html>
