<?php

namespace App\Repositories;

use App\Models\Product;

class StoreRepository
{
    /**
     * 🔍 Produtos com filtros
     */
    public function getFilteredProducts(array $filters)
    {
        $query = Product::query()
            ->with(['images', 'seo'])
            ->where('is_active', true);

        // 🔎 Busca por texto (PostgreSQL com unaccent)
        if (!empty($filters['search']) && mb_strlen($filters['search']) >= 3) {
            $searchTerm = '%' . $filters['search'] . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw("unaccent(description) ILIKE unaccent(?)", [$searchTerm])
                  ->orWhereRaw("unaccent(brand) ILIKE unaccent(?)", [$searchTerm]);
            });
        }

        // 💰 Preço mínimo
        if (!empty($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('sale_price', '>=', (float) $filters['min_price']);
        }

        // 💰 Preço máximo
        if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('sale_price', '<=', (float) $filters['max_price']);
        }

        // 🏷️ Marca
        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString(); // 🔥 mantém filtros na paginação
    }

    /**
     * ⭐ Produtos em destaque
     */
    public function getFeaturedProducts(int $limit = 5)
    {
        return Product::with(['images', 'seo'])
            ->where('is_active', true)
            ->where('is_featured', true) // 🔥 corrigido (antes estava random)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * 💸 Produtos mais baratos (promoção simples)
     */
    public function getOnSaleProducts(int $limit = 8)
    {
        return Product::with(['images'])
            ->where('is_active', true)
            ->orderBy('sale_price', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * 🏷️ Lista de marcas
     */
    public function getAllBrands()
    {
        return Product::query()
            ->whereNotNull('brand')
            ->where('is_active', true)
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
    }
}