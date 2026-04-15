@php
    $current = app()->getLocale();
    $segments = request()->segments();
    $rest = count($segments) > 0 ? '/'.implode('/', array_slice($segments, 1)) : '';
@endphp

<nav class="lang-switcher">
    <span>{{ __('language') }}:</span>
    @foreach (['en', 'vi'] as $locale)
        @if ($locale === $current)
            <strong>{{ strtoupper($locale) }}</strong>
        @else
            <a href="/{{ $locale }}{{ $rest }}">{{ strtoupper($locale) }}</a>
        @endif
    @endforeach
</nav>
