<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\PriceFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariantPriceController extends Controller
{
    public function __invoke(Request $request, Product $product): JsonResponse
    {
        $valueIds = collect($request->input('values', []))
            ->map(fn ($v) => (int) $v)
            ->sort()
            ->values()
            ->all();

        if (empty($valueIds)) {
            return response()->json(['error' => 'No values specified'], 422);
        }

        $variant = $product->variants()
            ->with('attributeValues')
            ->where('status', 'active')
            ->get()
            ->first(function ($v) use ($valueIds) {
                $existing = $v->attributeValues->pluck('id')->sort()->values()->all();
                return $existing === $valueIds;
            });

        if (!$variant) {
            return response()->json(['error' => 'Variant not found'], 404);
        }

        $effectivePrice = $variant->getEffectivePrice();

        return response()->json([
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'raw_price' => $effectivePrice,
            'price' => PriceFormatter::format($effectivePrice),
            'stock' => $variant->stock,
            'in_stock' => $variant->isInStock(),
            'image' => $variant->image ? asset('storage/' . $variant->image) : null,
        ]);
    }
}
