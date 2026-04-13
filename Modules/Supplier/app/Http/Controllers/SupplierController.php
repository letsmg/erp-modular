<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Supplier\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Helpers\SanitizerHelper;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::latest()->get();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $suppliers,
            ]);
        }
        
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'       => 'required|string|max:150',
            'cnpj'               => 'required|string|max:18|unique:suppliers,cnpj',
            'state_registration' => 'required|string|max:20',
            'address'            => 'required|string|max:150',
            'neighborhood'       => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'zip_code'           => 'required|string|max:10',
            'state'              => 'required|string|max:2',
            'email'              => 'required|email|unique:suppliers,email',
            'contact_name_1'     => 'required|string|max:100',
            'phone_1'            => 'required|string|max:20',
            'contact_name_2'     => 'nullable|string|max:100',
            'phone_2'            => 'nullable|string|max:20',
            'is_active'          => 'boolean',
        ], [
            'company_name.required' => 'A Razão Social é obrigatória.',
            'cnpj.required'         => 'O CNPJ é obrigatório.',
            'cnpj.unique'           => 'Este CNPJ já está cadastrado.',
            'zip_code.required'      => 'O CEP é obrigatório.',
            'contact_name_1.required' => 'O nome do contato principal é obrigatório.',
        ]);

        // Sanitiza todos os dados antes de salvar
        $data = SanitizerHelper::sanitize($data);

        $supplier = Supplier::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fornecedor cadastrado com sucesso!',
                'data' => $supplier,
            ], 201);
        }

        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor cadastrado com sucesso!');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Se a requisição contiver APENAS o campo is_active (mudança rápida no Index)
        if ($request->has('is_active') && count($request->all()) <= 2) {
            $supplier->update([
                'is_active' => $request->is_active
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status atualizado!',
                    'data' => $supplier->refresh(),
                ], 200);
            }
            
            return back()->with('message', 'Status atualizado!');
        }

        // Validação completa para o formulário de edição (Edit.vue)
        $data = $request->validate([
            'company_name'       => 'required|string|max:150',
            'cnpj'               => [
                'required', 
                'string', 
                'max:18', 
                Rule::unique('suppliers')->ignore($supplier->id)
            ],
            'state_registration' => 'required|string|max:20',
            'address'            => 'required|string|max:150',
            'neighborhood'       => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'zip_code'           => 'required|string|max:10',
            'state'              => 'required|string|max:2',
            'email'              => 'required|email|unique:suppliers,email,' . $supplier->id,
            'contact_name_1'     => 'required|string|max:100',
            'phone_1'            => 'required|string|max:20',
            'contact_name_2'     => 'nullable|string|max:100',
            'phone_2'            => 'nullable|string|max:20',
            'is_active'          => 'boolean',
        ], [
            'company_name.required' => 'A Razão Social é obrigatória.',
            'cnpj.unique'           => 'Este CNPJ já pertence a outro fornecedor.',
            'email.unique'          => 'Este email já está sendo usado por outro fornecedor.',
        ]);

        // Sanitiza todos os dados antes de salvar
        $data = SanitizerHelper::sanitize($data);

        $supplier->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso!',
                'data' => $supplier->refresh(),
            ], 200);
        }

        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor atualizado com sucesso!');
    }

    public function destroy(Supplier $supplier, Request $request)
    {
        $supplier->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fornecedor removido com sucesso!',
            ], 204);
        }
        
        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor removido com sucesso!');
    }
}
