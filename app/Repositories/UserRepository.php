<?php

namespace App\Repositories;

use App\Models\User;
use App\Helpers\SanitizerHelper;

class UserRepository
{
    public function getAllOrdered()
    {
        return User::orderBy('name')->get();
    }

    public function getNonAdmin()
    {
        return User::where('access_level', 0)
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