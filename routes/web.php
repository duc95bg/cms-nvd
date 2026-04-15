<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/'.app()->getLocale());
});

Route::prefix('{locale}')
    ->where(['locale' => 'en|vi'])
    ->group(function () {
        Route::get('/', function () {
            return view('home');
        })->name('home');

        Route::get('/about', function () {
            return view('about');
        })->name('about');
    });
