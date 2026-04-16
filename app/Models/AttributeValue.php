<?php

namespace App\Models;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    use HasFactory, HasLocalizedContent;

    protected $fillable = [
        'attribute_id',
        'value',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'array',
        'sort_order' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
