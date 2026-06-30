<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'category',
        'is_available',
        'stock',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
        'stock'        => 'integer',
    ];

    // Devuelve la URL completa, ya sea local (storage) o externa (http)
    public function getImageUrlFullAttribute(): string
    {
        if (!$this->image_url) {
            return '';
        }

        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }

        return Storage::url($this->image_url);
    }

    protected $appends = ['image_url_full'];
}