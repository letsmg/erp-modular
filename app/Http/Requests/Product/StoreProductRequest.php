<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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

            // Logística e Frete
            'weight'          => 'required|numeric|min:0.001', // Peso mínimo 1g
            'width'           => 'required|numeric|min:1',     // Medidas mínimas 1cm
            'height'          => 'required|numeric|min:1',
            'length'          => 'required|numeric|min:1',
            'free_shipping'   => 'boolean',
            
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
            
            // Imagens
            'images'          => 'required|array|min:1|max:6',
            'images.*'        => 'image|mimes:jpg,jpeg,png|max:2048', 
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Selecione um fornecedor.',
            'description.required' => 'A descrição do produto é obrigatória.',
            'weight.required'      => 'O peso é obrigatório para o cálculo de frete.',
            'width.required'       => 'A largura é obrigatória.',
            'height.required'      => 'A altura é obrigatória.',
            'length.required'      => 'O comprimento é obrigatório.',
            'images.required'      => 'Você precisa enviar pelo menos uma imagem.',
            'images.min'           => 'Envie ao menos :min imagem.',
            'images.*.max'         => 'Cada imagem não pode ser maior que 2MB.',
        ];
    }
}