<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\PriceFormatter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(string $locale, Request $request): View
    {
        // Placeholder — full implementation in issue #23
        abort(501, 'Not implemented yet');
    }

    public function byCategory(string $locale, string $slug): View
    {
        // Placeholder — full implementation in issue #23
        abort(501, 'Not implemented yet');
    }

    public function show(string $locale, string $slug): View
    {
        // Placeholder — full implementation in issue #24
        abort(501, 'Not implemented yet');
    }
}
