<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientRepository $repository,
        private readonly ClientService $service
    ) {}

    /**
     * Lista os clientes para o administrativo.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->only(['search', 'document_type', 'is_active', 'contributor_type', 'active', 'blocked']);
        $clients = $this->repository->getFiltered($filters);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'clients' => $clients,
                    'filters' => $filters,
                ],
            ]);
        }

        return inertia('Clients/Index', [
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    /**
     * Mostra o formulário de criação.
     */
    public function create()
    {
        $this->authorize('create', Client::class);

        return inertia('Clients/Create');
    }

    /**
     * Cadastra um novo cliente (admin).
     */
    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class);

        try {
            $data = $request->validated();
            $client = $this->service->create($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente cadastrado com sucesso!',
                    'data' => $client,
                ], 201);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Cliente cadastrado com sucesso!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao cadastrar cliente: ' . $e->getMessage(),
                ], 500);
            }
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Mostra detalhes do cliente.
     */
    public function show(Client $client, Request $request)
    {
        $this->authorize('view', $client);

        $client->load(['user', 'addresses', 'sales' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $client,
            ], 200);
        }

        return inertia('Clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * Mostra formulário de edição.
     */
    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return inertia('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Atualiza cliente.
     */
    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        try {
            $data = $request->validated();
            
            // Se tiver dados de usuário, atualiza também
            if (isset($data['user'])) {
                $userData = $data['user'];
                $updatedClient = $this->service->updateClientWithUser($client, $data, $userData);
            } else {
                $updatedClient = $this->repository->update($client, $data);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente atualizado com sucesso!',
                    'data' => $updatedClient->refresh(),
                ], 200);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Cliente atualizado com sucesso!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar cliente: ' . $e->getMessage(),
                ], 500);
            }
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Ativa/Inativa cliente.
     */
    public function toggleStatus(Client $client, Request $request)
    {
        $this->authorize('update', $client);

        try {
            $newStatus = !$client->is_active;
            $this->repository->update($client, ['is_active' => $newStatus]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Cliente " . ($newStatus ? 'ativado' : 'inativado') . " com sucesso!",
                    'data' => $client->refresh(),
                ], 200);
            }

            return redirect()->route('clients.index')
                ->with('success', "Cliente " . ($newStatus ? 'ativado' : 'inativado') . " com sucesso!");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao alterar status: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Exclui cliente.
     */
    public function destroy(Client $client, Request $request)
    {
        $this->authorize('delete', $client);

        try {
            $this->repository->delete($client);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente excluído com sucesso!',
                ], 204);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Cliente excluído com sucesso!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir cliente: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Erro ao excluir cliente: ' . $e->getMessage());
        }
    }

    /**
     * Busca clientes.
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->get('search');
        $clients = $this->repository->search($search);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Valida documento (CPF/CNPJ)
     */
    public function validateDocument(Request $request): JsonResponse
    {
        $document = $request->input('document');
        
        if (!$document) {
            return $this->error('Documento é obrigatório.');
        }

        $documentType = $this->service->getDocumentType($document);
        $isValid = false;

        if ($documentType === 'CPF') {
            $isValid = $this->service->isValidCPF($document);
        } elseif ($documentType === 'CNPJ') {
            $isValid = $this->service->isValidCNPJ($document);
        }

        return $isValid ? $this->success([
            'document_type' => $documentType,
            'valid' => $isValid,
            'document' => $document,
        ], 'Documento válido.') : $this->validation([
            'document_type' => $documentType,
            'valid' => $isValid,
            'document' => $document,
        ], 'Documento inválido.');
    }
}
