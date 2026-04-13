<?php

use Modules\Client\Http\Controllers\ClientController;
use App\Http\Controllers\ShoppingCartController;
use Modules\User\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use Modules\Supplier\Http\Controllers\SupplierController;
use Modules\Product\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */
    
    Route::post('/login', [LoginController::class, 'apiLogin'])->name('api.login');
    
    /*
    |--------------------------------------------------------------------------
    | CLIENTS
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('clients', ClientController::class)->names([
            'index' => 'api.clients.index',
            'store' => 'api.clients.store',
            'show' => 'api.clients.show',
            'update' => 'api.clients.update',
            'destroy' => 'api.clients.destroy'
        ]);
    
    Route::prefix('clients')->group(function () {
        Route::post('/validate-document', [ClientController::class, 'validateDocument'])
            ->name('api.clients.validate-document');
            
        Route::get('/search', [ClientController::class, 'search'])
            ->name('api.clients.search');
            
        Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])
            ->name('api.clients.toggle-status');
            
        Route::get('/export', [ClientController::class, 'export'])
            ->name('api.clients.export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | SHOPPING CART
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth')->prefix('shopping-cart')->group(function () {
        Route::get('/', [ShoppingCartController::class, 'index'])
            ->name('shopping-cart.index');
            
        Route::post('/', [ShoppingCartController::class, 'store'])
            ->name('shopping-cart.store');
            
        Route::get('/summary', [ShoppingCartController::class, 'summary'])
            ->name('shopping-cart.summary');
            
        Route::post('/shipping', [ShoppingCartController::class, 'shipping'])
            ->name('shopping-cart.shipping');
            
        Route::post('/checkout', [ShoppingCartController::class, 'checkout'])
            ->name('shopping-cart.checkout');
            
        Route::delete('/clear', [ShoppingCartController::class, 'clear'])
            ->name('shopping-cart.clear');
    });
    
    Route::middleware('auth')->prefix('shopping-cart')->group(function () {
        Route::put('/{cart_item}', [ShoppingCartController::class, 'update'])
            ->name('shopping-cart.update');
            
        Route::delete('/{cart_item}', [ShoppingCartController::class, 'destroy'])
            ->name('shopping-cart.destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | PRODUCTS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth', 'staff'])->group(function () {
        Route::apiResource('products', ProductController::class)->names([
            'index' => 'api.products.index',
            'store' => 'api.products.store',
            'show' => 'api.products.show',
            'update' => 'api.products.update',
            'destroy' => 'api.products.destroy'
        ]);
        
        Route::prefix('products')->group(function () {
            Route::get('/{product}/preview', [ProductController::class, 'preview'])
                ->name('api.products.preview');
                
            Route::patch('/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])
                ->name('api.products.toggle-featured');
                
            Route::patch('/{product}/toggle', [ProductController::class, 'toggle'])
                ->name('api.products.toggle');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | SUPPLIERS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth', 'staff'])->group(function () {
        Route::apiResource('suppliers', SupplierController::class)->names([
            'index' => 'api.suppliers.index',
            'store' => 'api.suppliers.store',
            'show' => 'api.suppliers.show',
            'update' => 'api.suppliers.update',
            'destroy' => 'api.suppliers.destroy'
        ]);
    });
    
    /*
    |--------------------------------------------------------------------------
    | USERS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth', 'staff'])->group(function () {
        Route::apiResource('users', UserController::class)->names([
            'index' => 'api.users.index',
            'store' => 'api.users.store',
            'show' => 'api.users.show',
            'update' => 'api.users.update',
            'destroy' => 'api.users.destroy'
        ]);
    });
    
    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER INFO
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth')->group(function () {
        Route::get('/me', function () {
            return response()->json([
                'success' => true,
                'data' => Auth::user(),
            ]);
        })->name('api.me');
        
        Route::post('/logout', [LoginController::class, 'apiLogout'])->name('api.logout');
    });
});
