<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id();
            
            // As colunas 'seoable_id' e 'seoable_type' que permitem a polimorfia
            // indexable() e nullable() garantem que funcione para páginas avulsas
            $table->nullableMorphs('seoable'); 

            $table->string('slug')->unique(); // ex: 'promocao-natal-2026'
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
