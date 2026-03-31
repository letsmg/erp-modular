<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanitizationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sanitizes_product_update_data()
    {
        $user = User::factory()->create(['access_level' => 1]);
        $product = Product::factory()->create();

        $data = [
            'description' => '<p>Updated <b>description</b> with <script>alert("xss")</script> tags</p>',
            'brand' => '  Updated Brand  ',
            'schema_markup' => '<script type="application/ld+json">{"@context": "https://schema.org","name":"Updated Product"}</script>',
            'google_tag_manager' => '<!-- Updated GTM --><script>console.log("test");</script>',
        ];

        $response = $this->actingAs($user)->put(route('products.update', $product), $data);

        $response->assertRedirect();
        
        $product->refresh();
        
        // Verifica se os campos foram sanitizados
        $this->assertEquals('Updated description with tags', $product->description);
        $this->assertEquals('Updated Brand', $product->brand);
        
        // Verifica se os campos HTML foram preservados
        $this->assertEquals('<script type="application/ld+json">{"@context": "https://schema.org","name":"Updated Product"}</script>', $product->seo->schema_markup);
        $this->assertEquals('<!-- Updated GTM --><script>console.log("test");</script>', $product->seo->google_tag_manager);
    }

    /** @test */
    public function it_sanitizes_supplier_creation_data()
    {
        $user = User::factory()->create();

        $data = [
            'company_name' => '<b>Test</b> Company <script>alert("xss")</script> LTDA',
            'cnpj' => '12.345.678/0001-90',
            'state_registration' => '123456789',
            'email' => 'test@company.com',
            'address' => '<p>Rua de Teste, 123</p>',
            'neighborhood' => '  Centro  ',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01001-000',
            'contact_name_1' => '<i>João</i> Silva',
            'phone_1' => '(11) 99999-9999',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->post(route('suppliers.store'), $data);

        $response->assertRedirect();
        
        $supplier = \App\Models\Supplier::where('cnpj', '12.345.678/0001-90')->first();
        
        // Verifica se os campos foram sanitizados
        $this->assertEquals('Test Company LTDA', $supplier->company_name);
        $this->assertEquals('Rua de Teste, 123', $supplier->address);
        $this->assertEquals('Centro', $supplier->neighborhood);
        $this->assertEquals('João Silva', $supplier->contact_name_1);
    }

    /** @test */
    public function it_preserves_get_requests()
    {
        $user = User::factory()->create();

        // GET requests não devem ser afetadas
        $response = $this->actingAs($user)->get(route('products.index', [
            'search' => '<script>alert("xss")</script>test'
        ]));

        $response->assertStatus(200);
    }
}
