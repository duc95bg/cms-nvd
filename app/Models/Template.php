<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'view',
        'default_content',
    ];

    protected $casts = [
        'default_content' => 'array',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
