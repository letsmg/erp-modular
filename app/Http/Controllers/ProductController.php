<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(ProductService $service, ProductRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => $this->repository->getAll(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => $this->repository->getActiveSuppliers()
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->service->storeProduct($request->validated(), $request);
        return redirect()->route('products.index')->with('message', 'Produto cadastrado!');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product->load(['images', 'seo']),
            'suppliers' => $this->repository->getActiveSuppliers()
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->service->updateProduct($product, $request->validated(), $request);
        return redirect()->route('products.index')->with('message', 'Produto atualizado!');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('message', 'Status atualizado!');
    }

    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product);
        return redirect()->route('products.index')->with('message', 'Removido com sucesso.');
    }
}