<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('about') }}</title>
</head>
<body>
    @include('partials.lang-switcher')
    <h1>{{ __('about') }}</h1>
    <p><a href="/{{ app()->getLocale() }}">{{ __('home') }}</a></p>
</body>
</html>
