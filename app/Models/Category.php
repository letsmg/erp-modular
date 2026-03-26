<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'is_active'
    ];    

    /**
     * 🔗 Relação com produtos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * 🌳 Categoria pai
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 🌿 Subcategorias
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 🔥 Geração automática de slug
     */
    protected static function booted()
    {
        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });
    }

    /**
     * 🔥 Slug único
     */
    public static function generateUniqueSlug($text)
    {
        $baseSlug = Str::slug($text);
        $slug = $baseSlug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }
}