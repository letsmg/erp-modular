<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Quem pode ver a listagem (Index)
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->access_level, [0, 1]);
    }

    /**
     * Quem pode cadastrar
     */
    public function create(User $user): bool
    {
        return in_array($user->access_level, [0, 1]);
    }

    /**
     * Quem pode deletar (Apenas Nível 1)
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->access_level === 1;
    }

    /**
     * Quem pode alternar destaque/ativo (Apenas Nível 1)
     */
    public function toggle(User $user): bool
    {
        return $user->access_level === 1;
    }

    public function update(User $user, Product $product): bool
    {
        // Ambos os níveis podem editar os dados básicos
        return in_array($user->access_level, [0, 1]);
    }
}