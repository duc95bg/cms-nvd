<?php

namespace App\Models;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasLocalizedContent;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'base_price',
        'status',
        'featured',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'short_description' => 'array',
        'base_price' => 'decimal:2',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();
    }

    /**
     * Get the effective price — variant price if given, otherwise base_price.
     */
    public function getEffectivePrice(?ProductVariant $variant = null): float
    {
        if ($variant && $variant->price !== null) {
            return (float) $variant->price;
        }

        return (float) $this->base_price;
    }

    /**
     * Get min/max price across all active variants.
     */
    public function getPriceRange(): array
    {
        $variantPrices = $this->variants
            ->where('status', 'active')
            ->pluck('price')
            ->filter(fn ($p) => $p !== null)
            ->map(fn ($p) => (float) $p)
            ->all();

        if (empty($variantPrices)) {
            return ['min' => (float) $this->base_price, 'max' => (float) $this->base_price];
        }

        $variantPrices[] = (float) $this->base_price;

        return ['min' => min($variantPrices), 'max' => max($variantPrices)];
    }

    /**
     * Whether any active variant has stock > 0.
     */
    public function isInStock(): bool
    {
        if ($this->variants->isEmpty()) {
            return true;
        }

        return $this->variants
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->isNotEmpty();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}
