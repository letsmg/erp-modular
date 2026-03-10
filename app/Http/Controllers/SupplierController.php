<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Exibe a listagem com suporte a busca em tempo real.
     */
    public function index(Request $request)
    {
        // 1. Iniciamos a consulta (Query Builder)
        $query = Supplier::query();

        // 2. Verificamos se existe um termo de busca vindo da URL (?search=...)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('company_name', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
        }

        // 3. Retornamos a renderização para o Vue (Inertia)
        return Inertia::render('Suppliers/SuppliersIndex', [
            'suppliers' => $query->latest()->get(),
            'filters' => $request->only(['search']) // Mantém o texto no input após a busca
        ]);
    }

    /**
     * Processa o cadastro de um novo fornecedor.
     */
    public function store(Request $request)
    {
        // Validação dos dados (Crucial para integridade do banco PostgreSQL)
        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'cnpj' => 'required|string|unique:suppliers,cnpj|max:18',
            'state_registration' => 'required|string|max:20',
            'phone_1' => 'required|string|max:20',
            'contact_name_1' => 'required|string|max:100',
        ]);

        // Criação no banco
        Supplier::create($validated);

        // Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('suppliers.index')
                         ->with('message', 'Fornecedor cadastrado com sucesso!');
    }
}