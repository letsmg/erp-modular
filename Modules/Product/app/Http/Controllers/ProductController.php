<?php

namespace Modules\Product\Http\Controllers;

use Modules\Product\Models\Product;
use Modules\Product\Http\Requests\Product\StoreProductRequest;
use Modules\Product\Services\ProductService;
use Modules\Product\Repositories\ProductRepository;
use Inertia\Inertia;
use Modules\Supplier\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    protected $service;
    protected $repository;
    use AuthorizesRequests;

    public function __construct(ProductService $service, ProductRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Lista os produtos com suporte a busca e filtros.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->all([
            'search', 'blocked', 'active', 'sort',
            'brand', 'model', 'category_id',
            'price_min', 'price_max', 'stock_min', 'stock_max'
        ]);
        
        // API Request - retorna JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $this->repository->getFiltered($filters),
                    'filters' => $filters,
                    'brands' => $this->repository->getAllBrands(),
                    'categories' => $this->repository->getAllCategories(),
                ],
            ]);
        }
        
        return Inertia::render('Products/Index', [
            'products' => $this->repository->getFiltered($filters),
            'filters' => $filters,
            'brands' => $this->repository->getAllBrands(),
            'categories' => $this->repository->getAllCategories(),
        ]);
    }

    /**
     * Exibe o formulário de criação com a lista de fornecedores.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => $this->repository->getActiveSuppliers(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Salva um novo produto.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->service->storeProduct($request->validated(), $request);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'data' => $product,
            ], 201);
        }
        
        return redirect()->route('products.index')->with('message', 'Produto cadastrado!');
    }

    /**
     * Exibe o formulário de edição com SEO e Imagens carregados.
     */
    public function edit(Product $product) 
    {
        $product->load(['seo', 'images']);
        
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'suppliers' => Supplier::all()
        ]);
    }

    /**
     * Atualiza o produto via Service.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        $this->service->updateProduct($product, $request->all(), $request);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'data' => $product->refresh(),
            ], 200);
        }

        return redirect()->route('products.index')
            ->with('message', 'Produto atualizado com sucesso!');
    }

    /**
     * Alterna o status de ativação (is_active).
     */
    public function toggle(Product $product)
    {
        $this->authorize('toggle', $product);

        $product->update(['is_active' => !$product->is_active]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status de ativação atualizado!',
                'data' => $product->refresh(),
            ], 200);
        }

        return back()->with('message', 'Status de ativação atualizado!');
    }

    /**
     * Alterna o status de destaque (is_featured). 
     */
    public function toggleFeatured(Product $product)
    {
        $this->authorize('toggle', $product);
        
        $updatedProduct = $this->repository->toggleFeatured($product);
        
        return response()->json([
            'success' => true,
            'message' => 'Status de destaque atualizado!',
            'product' => $updatedProduct
        ]);
    }

    /**
     * Remove o produto e seus arquivos.
     */
    public function destroy(Product $product, Request $request)
    {
        $this->authorize('delete', $product);

        $this->service->deleteProduct($product);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produto removido com sucesso!',
            ], 204);
        }
        
        return redirect()->route('products.index')->with('message', 'Removido com sucesso.');
    }

    /**
     * Renderiza a visualização prévia do produto.
     */
    public function preview(Product $product, Request $request)
    {
        $product->load(['supplier', 'images']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);
        }

        return Inertia::render('Products/Preview', [
            'product' => $product
        ]);
    }
}