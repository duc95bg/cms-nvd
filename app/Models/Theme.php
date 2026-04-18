<?php

namespace App\Models;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasLocalizedContent;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'blocks_preset',
        'status',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'blocks_preset' => 'array',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
