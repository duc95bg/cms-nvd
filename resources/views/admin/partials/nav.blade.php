<nav class="bg-gray-800 text-white">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.dashboard') }}" class="font-bold text-lg">{{ __('Dashboard') }}</a>
            <div class="flex gap-4 text-sm">
                @php $current = request()->route()->getName(); @endphp
                @foreach([
                    'admin.dashboard' => __('Dashboard'),
                    'admin.products.index' => __('Products'),
                    'admin.categories.index' => __('Categories'),
                    'admin.attributes.index' => __('Attributes'),
                    'admin.orders.index' => __('Orders'),
                    'admin.sites.index' => __('Your sites'),
                    'admin.settings.edit' => __('Settings'),
                ] as $route => $label)
                    <a href="{{ route($route) }}"
                       class="{{ $current === $route ? 'text-white font-medium' : 'text-gray-400 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span class="text-gray-400">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-gray-400 hover:text-white">{{ __('logout') }}</button>
            </form>
        </div>
    </div>
</nav>
