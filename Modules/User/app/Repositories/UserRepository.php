<?php

namespace Modules\User\Repositories;

use Modules\User\Models\User;
use App\Helpers\SanitizerHelper;

class UserRepository
{
    public function getAllOrdered()
    {
        $query = User::where('access_level', '!=', 2)  // Exclui apenas clientes
            ->orderBy('name');
        
        // DEBUG: Mostrar SQL e bindings
        // dd([
        //     'sql' => $query->toSql(),
        //     'bindings' => $query->getBindings(),
        //     'count' => $query->count(),
        //     'results' => $query->get()->toArray()
        // ]);
        
        return $query->get();
    }

    public function getNonAdmin()
    {
        return User::where('access_level', 0)
            ->orderBy('name')
            ->get();
    }

    public function getNonAdminWithSelf($currentUserId)
    {
        return User::where(function($query) use ($currentUserId) {
                $query->where('access_level', 0)  // Todos operadores
                      ->orWhere('id', $currentUserId);  // + ele mesmo
            })
            ->where('access_level', '!=', 2)  // Exclui clientes
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): User
    {
        // Sanitiza todos os dados antes de criar
        $data = SanitizerHelper::sanitize($data);
        
        return User::create($data);
    }

    public function update(User $user, array $data): void
    {
        // Sanitiza todos os dados antes de atualizar
        $data = SanitizerHelper::sanitize($data);
        
        $user->update($data);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
