<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SelfClientPolicy
{
    /**
     * Determine whether the user can view their own client data.
     */
    public function view(User $user, Client $client): Response
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('Você só pode visualizar seus próprios dados.');
    }

    /**
     * Determine whether the user can update their own client data.
     */
    public function update(User $user, Client $client): Response
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('Você só pode atualizar seus próprios dados.');
    }

    /**
     * Determine whether the user can delete their own account.
     */
    public function delete(User $user, Client $client): Response
    {
        if ($user->id !== $client->user_id) {
            return Response::deny('Você só pode excluir sua própria conta.');
        }

        // Verifica se tem compras nos últimos 5 anos
        $hasRecentPurchases = \App\Models\Sale::where('client_id', $client->id)
            ->where('created_at', '>=', now()->subYears(5))
            ->exists();

        if ($hasRecentPurchases) {
            return Response::deny('Você não pode excluir sua conta pois possui compras recentes (menos de 5 anos).');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can manage their addresses.
     */
    public function manageAddresses(User $user, Client $client): Response
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('Você só pode gerenciar seus próprios endereços.');
    }

    /**
     * Determine whether the user can deactivate their account.
     */
    public function deactivate(User $user, Client $client): Response
    {
        if ($user->id !== $client->user_id) {
            return Response::deny('Você só pode inativar sua própria conta.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can register a new client account.
     */
    public function create(User $user): Response
    {
        // Usuário não pode ter mais de um cliente associado
        $existingClient = Client::where('user_id', $user->id)->first();
        
        return $existingClient === null
            ? Response::allow()
            : Response::deny('Você já possui um cadastro de cliente.');
    }
}
