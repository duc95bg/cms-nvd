<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'slug',
        'content',
        'published',
    ];

    protected $casts = [
        'content' => 'array',
        'published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function t(string $key, ?string $locale = null, string $default = ''): string
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return (string) (
            data_get($this->content, $key.'.'.$locale)
            ?? data_get($this->content, $key.'.'.$fallback)
            ?? $default
        );
    }
}
