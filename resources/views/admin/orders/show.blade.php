<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Order details') }} — {{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">{{ $order->order_number }}</h1>
                <p class="text-gray-500 text-sm">{{ __('Ordered on') }} {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

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

        <div class="grid md:grid-cols-3 gap-6 mb-8">
            {{-- Customer info --}}
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold mb-3">{{ __('Customer information') }}</h2>
                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-500">{{ __('Full name') }}:</span> {{ $order->customer_name }}</div>
                    <div><span class="text-gray-500">{{ __('Email') }}:</span> {{ $order->customer_email }}</div>
                    <div><span class="text-gray-500">{{ __('Phone') }}:</span> {{ $order->customer_phone }}</div>
                    <div><span class="text-gray-500">{{ __('Shipping address') }}:</span> {{ $order->customer_address }}</div>
                    @if ($order->customer_notes)
                        <div><span class="text-gray-500">{{ __('Notes') }}:</span> {{ $order->customer_notes }}</div>
                    @endif
                    @if ($order->user)
                        <div class="pt-2 border-t"><span class="text-gray-500">{{ __('Account') }}:</span> {{ $order->user->email }}</div>
                    @endif
                </div>
            </div>

            {{-- Payment info --}}
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold mb-3">{{ __('Payment info') }}</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('Payment method') }}:</span>
                        <span class="px-2 py-0.5 bg-gray-100 rounded text-xs uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('Payment status') }}:</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $paymentColors[$order->payment_status] ?? '' }}">
                            {{ __(ucfirst($order->payment_status)) }}
                        </span>
                    </div>
                    <div class="pt-2 border-t">
                        <span class="text-gray-500">{{ __('Grand total') }}:</span>
                        <span class="font-bold text-lg text-blue-600">{{ \App\Support\PriceFormatter::format($order->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Status update --}}
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold mb-3">{{ __('Update status') }}</h2>
                <div class="mb-3">
                    <span class="text-gray-500 text-sm">{{ __('Current') }}:</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? '' }}">
                        {{ __(ucfirst($order->status)) }}
                    </span>
                </div>
                @if ($allowedTransitions->isNotEmpty())
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-3">
                        @csrf
                        <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                            @foreach ($allowedTransitions as $status)
                                <option value="{{ $status }}">{{ __(ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                        <textarea name="note" rows="2" placeholder="{{ __('Note (optional)') }}"
                                  class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            {{ __('Update status') }}
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">{{ __('No status changes available') }}</p>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border overflow-hidden mb-8">
            <h2 class="font-semibold p-6 pb-3">{{ __('Items') }}</h2>
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">{{ __('Product') }}</th>
                        <th class="px-6 py-3">{{ __('Unit price') }}</th>
                        <th class="px-6 py-3">{{ __('Quantity') }}</th>
                        <th class="px-6 py-3">{{ __('Line total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-medium">{{ $item->product_name }}</div>
                                @if ($item->variant_info)
                                    <div class="text-xs text-gray-500 mt-1">
                                        @foreach ($item->variant_info as $attr)
                                            <span class="inline-block px-2 py-0.5 bg-gray-100 rounded mr-1">
                                                {{ $attr['attribute'] ?? '' }}: {{ $attr['value'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3">{{ \App\Support\PriceFormatter::format($item->product_price) }}</td>
                            <td class="px-6 py-3">{{ $item->quantity }}</td>
                            <td class="px-6 py-3 font-semibold">{{ \App\Support\PriceFormatter::format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right font-semibold">{{ __('Grand total') }}</td>
                        <td class="px-6 py-3 font-bold text-blue-600">{{ \App\Support\PriceFormatter::format($order->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Status timeline --}}
        <div class="bg-white rounded-xl border p-6">
            <h2 class="font-semibold mb-4">{{ __('Status history') }}</h2>
            @forelse ($order->statusLogs as $log)
                <div class="flex items-start gap-3 mb-4 last:mb-0">
                    <div class="w-3 h-3 rounded-full mt-1.5 shrink-0 {{ str_replace('text-', 'bg-', explode(' ', $statusColors[$log->new_status] ?? 'bg-gray-400')[0]) }}"></div>
                    <div>
                        <div class="font-medium">{{ __(ucfirst($log->new_status)) }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                            @if ($log->changedBy)
                                — {{ $log->changedBy->name }}
                            @endif
                        </div>
                        @if ($log->note)
                            <div class="text-sm text-gray-600 mt-1">{{ $log->note }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No status history') }}</p>
            @endforelse
        </div>
    </div>
</body>
</html>
