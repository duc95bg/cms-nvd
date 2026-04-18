@extends('layouts.catalog')

@section('title', $site->t('seo.title', null, $site->slug) . ' — ' . config('app.name'))
@section('description', $site->t('seo.description'))

@section('content')
    {!! $blocksHtml !!}
@endsection
