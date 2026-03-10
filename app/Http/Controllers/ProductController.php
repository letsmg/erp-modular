<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        // Buscando produtos (no futuro aqui entra a lógica de cache)
        $products = Product::with('supplier')->latest()->get();
        
        return Inertia::render('Products/ProductsIndex', [
            'products' => $products
        ]);
    }

 

    public function store(Request $request)
    {
        // Validação combinada
        $validated = $request->validate([
            'description' => 'required',
            'sale_price' => 'required|numeric',
            'supplier_id' => 'required|exists:suppliers,id',
            // Validações do SEO
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        // Usamos Transaction para garantir que ou salva os dois, ou nenhum
        DB::transaction(function () use ($request, $validated) {
            // 1. Cria o Produto
            $product = Product::create($validated);

            // 2. Cria o SEO vinculado (Polimorfismo)
            $product->seo()->create([
                'slug' => $product->slug, // Aproveita o slug gerado no boot
                'title' => $request->meta_title,
                'description' => $request->meta_description,
                'keywords' => $request->meta_keywords,
            ]);
        });

        return redirect()->route('products.index')->with('message', 'Produto e SEO cadastrados!');
    }
}