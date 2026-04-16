@php
    $supported = ['en', 'vi'];
    $current = app()->getLocale();
    $segments = request()->segments();
    $firstIsLocale = isset($segments[0]) && in_array($segments[0], $supported, true);
@endphp

<nav class="lang-switcher">
    <span>{{ __('language') }}:</span>
    @foreach ($supported as $locale)
        @if ($locale === $current)
            <strong>{{ strtoupper($locale) }}</strong>
        @else
            @if ($firstIsLocale)
                {{-- Public locale-prefixed route: swap the first segment, keep the rest. --}}
                @php
                    $rest = count($segments) > 1 ? '/'.implode('/', array_slice($segments, 1)) : '';
                @endphp
                <a href="/{{ $locale }}{{ $rest }}">{{ strtoupper($locale) }}</a>
            @else
                {{-- Non-locale-prefixed route (e.g. admin preview): use ?locale= query param. --}}
                <a href="{{ request()->fullUrlWithQuery(['locale' => $locale]) }}">{{ strtoupper($locale) }}</a>
            @endif
        @endif
    @endforeach
</nav>
