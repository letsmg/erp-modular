<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Dados Básicos
            'supplier_id'    => 'required|exists:suppliers,id',
            'description'     => 'required|string|max:255',            
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'cost_price'      => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            
            // Status, Destaque e Logística
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
            'free_shipping'   => 'boolean',
            'weight'          => 'required|numeric|min:0',
            'width'           => 'required|numeric|min:0',
            'height'          => 'required|numeric|min:0',
            'length'          => 'required|numeric|min:0',

            // SEO
            'meta_title'        => 'nullable|string|max:70',
            'meta_description'  => 'nullable|string|max:160',
            'meta_keywords'     => 'nullable|string',
            'canonical_url'     => 'nullable|url',
            'h1'                => 'nullable|string',
            'text1'             => 'nullable|string',
            'h2'                => 'nullable|string',
            'text2'             => 'nullable|string',
            'schema_markup'     => 'nullable|string',
            'google_tag_manager'=> 'nullable|string',
            'ads'               => 'nullable|string',

            // Gestão de Imagens
            'existing_images'   => 'nullable|array',
            'existing_images.*.id' => 'required|integer|exists:product_images,id',
            
            'new_images'        => 'nullable|array|max:6',
            'new_images.*'      => 'image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição não pode ficar vazia.',
            'weight.min'           => 'O peso deve ser maior que zero.',
            'cost_price.min'       => 'O preço de custo não pode ser negativo.',
            'new_images.max'       => 'Você pode carregar no máximo 6 novas imagens.',
        ];
    }

    protected function passedValidation()
    {
        // Garante que o produto não fique sem imagens
        $totalImages = count($this->existing_images ?? []) + count($this->file('new_images') ?? []);
        
        if ($totalImages < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'new_images' => 'O produto deve manter pelo menos uma imagem ativa.'
            ]);
        }
    }
}