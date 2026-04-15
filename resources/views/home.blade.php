<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('home') }}</title>
</head>
<body>
    @include('partials.lang-switcher')
    <h1>{{ __('welcome') }}</h1>
    <p><a href="/{{ app()->getLocale() }}/about">{{ __('about') }}</a></p>
</body>
</html>
