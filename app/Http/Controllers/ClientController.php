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

            return redirect()->route('clients.index')
                ->with('success', 'Cliente cadastrado com sucesso!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Mostra detalhes do cliente.
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['user', 'addresses', 'sales' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

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

            return redirect()->route('clients.index')
                ->with('success', 'Cliente atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Ativa/Inativa cliente.
     */
    public function toggleStatus(Client $client)
    {
        $this->authorize('update', $client);

        try {
            $newStatus = !$client->is_active;
            $this->repository->update($client, ['is_active' => $newStatus]);

            return redirect()->route('clients.index')
                ->with('success', "Cliente " . ($newStatus ? 'ativado' : 'inativado') . " com sucesso!");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Exclui cliente.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        try {
            $this->repository->delete($client);

            return redirect()->route('clients.index')
                ->with('success', 'Cliente excluído com sucesso!');
        } catch (\Exception $e) {
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
