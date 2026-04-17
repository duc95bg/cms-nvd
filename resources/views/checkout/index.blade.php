@extends('layouts.catalog')

@section('title', __('Checkout') . ' — ' . config('app.name'))

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Checkout') }}</h1>

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        <form method="POST" action="/checkout/process" class="grid md:grid-cols-3 gap-8">
            @csrf

            {{-- Left: customer form --}}
            <div class="md:col-span-2 bg-white rounded-xl border p-6 space-y-5">
                <h2 class="text-xl font-semibold mb-2">{{ __('Customer information') }}</h2>

                <div>
                    <label class="block font-medium mb-1">{{ __('Full name') }} *</label>
                    <input type="text" name="customer_name" required
                           value="{{ old('customer_name', auth()->user()?->name) }}"
                           class="w-full border rounded-lg px-3 py-2">
                    @error('customer_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Email') }} *</label>
                        <input type="email" name="customer_email" required
                               value="{{ old('customer_email', auth()->user()?->email) }}"
                               class="w-full border rounded-lg px-3 py-2">
                        @error('customer_email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Phone') }} *</label>
                        <input type="tel" name="customer_phone" required
                               value="{{ old('customer_phone') }}"
                               class="w-full border rounded-lg px-3 py-2">
                        @error('customer_phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block font-medium mb-1">{{ __('Shipping address') }} *</label>
                    <textarea name="customer_address" required rows="3"
                              class="w-full border rounded-lg px-3 py-2">{{ old('customer_address') }}</textarea>
                    @error('customer_address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-medium mb-1">{{ __('Notes') }}</label>
                    <textarea name="customer_notes" rows="2"
                              class="w-full border rounded-lg px-3 py-2">{{ old('customer_notes') }}</textarea>
                </div>

                <div>
                    <h3 class="font-semibold mb-3">{{ __('Payment method') }}</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                            <span>{{ __('Cash on delivery') }}</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                            <span>{{ __('Bank transfer') }}</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="vnpay" {{ old('payment_method') === 'vnpay' ? 'checked' : '' }}>
                            <span>VNPay</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="paypal" {{ old('payment_method') === 'paypal' ? 'checked' : '' }}>
                            <span>PayPal</span>
                        </label>
                    </div>
                    @error('payment_method')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-lg">
                    {{ __('Place order') }}
                </button>
            </div>

            {{-- Right: order summary --}}
            <div class="bg-white rounded-xl border p-6 h-fit sticky top-8">
                <h2 class="text-xl font-semibold mb-4">{{ __('Order summary') }}</h2>

                <div class="space-y-3 divide-y">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-2 pt-3 first:pt-0">
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ $item->product->t('name') }}</div>
                                @if ($item->variant)
                                    <div class="text-xs text-gray-500">
                                        @foreach ($item->variant->attributeValues as $av)
                                            {{ $av->t('value') }}@if (!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500">× {{ $item->quantity }}</div>
                            </div>
                            <div class="text-sm font-semibold whitespace-nowrap">
                                {{ \App\Support\PriceFormatter::format($item->line_total) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t mt-4 pt-4 flex justify-between items-center">
                    <span class="font-semibold">{{ __('Grand total') }}</span>
                    <span class="text-xl font-bold text-blue-600">{{ \App\Support\PriceFormatter::format($total) }}</span>
                </div>
            </div>
        </form>
    </div>
@endsection
