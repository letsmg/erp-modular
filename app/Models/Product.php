<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    // Campos que podem ser preenchidos em massa
        protected $fillable = [
        'supplier_id',
        'description',
        'brand',
        'model',
        'size',
        'collection',
        'gender',
        'cost_price',
        'sale_price',
        'barcode',
        'stock_quantity',
        'is_active',
        'slug',     
    ];

    /**
     * Relacionamento: Um produto pertence a um fornecedor.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Boot function do Model.
     * Usada para executar lógica automaticamente em eventos do Eloquent.
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de criar um produto, gera o SLUG para a URL da vitrine
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->description) . '-' . uniqid();
            }
        });
    }

    public function seo()
    {
        // Um produto tem um SEO (polimórfico)
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }
}