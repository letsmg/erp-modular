<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('description', 150);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('size', 10);
            $table->string('collection', 50);
            $table->string('gender', 20);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->string('barcode', 50);
            $table->integer('stock_quantity');
            $table->boolean('is_active')->default(true);
            
            // Campos de Vitrine e SEO (Item 2 do seu pedido)
            $table->string('slug')->unique(); // URL amigável ex: /produto/tenis-nike-air
            $table->string('seo_title')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('seo_description')->nullable();
            
            // Fotos (Simplificando conforme seu SQL)
            $table->json('images')->nullable(); 

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};