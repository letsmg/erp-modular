<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Supplier;

class ProductRepository
{
    public function getAll()
    {
        return Product::with(['supplier:id,company_name', 'images'])
            ->latest()
            ->get();
    }

    public function getActiveSuppliers()
    {
        return Supplier::select('id', 'company_name')->orderBy('company_name')->get();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        return $product->delete();
    }
}