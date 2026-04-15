<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public const SUPPORTED = ['en', 'vi'];
    public const DEFAULT = 'vi';

    public function handle(Request $request, Closure $next)
    {
        $segment = $request->segment(1);

        if (in_array($segment, self::SUPPORTED, true)) {
            $locale = $segment;
            $request->session()->put('locale', $locale);
        } else {
            $locale = $request->session()->get('locale', self::DEFAULT);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
