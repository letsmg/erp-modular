<?php

namespace Modules\User\Services;

use Modules\User\Models\User;
use Modules\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function list($currentUser)
    {
        if ($currentUser->access_level->value !== 1) {
            // Para usuários não-admin (clientes e padrão), mostra todos os usuários não-admin + ele mesmo
            return $this->repository->getNonAdminWithSelf($currentUser->id);
        }

        // Para admin, mostra todos os usuários (admins + padrão + clientes)
        return $this->repository->getAllOrdered();
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function update(User $user, array $data): void
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->repository->update($user, $data);
    }

    public function toggleStatus(User $user, $currentUser): void
    {
        if ($user->id === $currentUser->id) {
            throw ValidationException::withMessages([
                'error' => 'Você não pode desativar a si mesmo.'
            ]);
        }

        $this->repository->update($user, [
            'is_active' => !$user->is_active
        ]);
    }

    public function resetPassword(User $user): void
    {
        $this->repository->update($user, [
            'password' => Hash::make('Mudar@123')
        ]);
    }

    public function delete(User $user, $currentUser): void
    {
        if ($user->id === $currentUser->id) {
            throw ValidationException::withMessages([
                'error' => 'Você não pode se excluir.'
            ]);
        }

        $this->repository->delete($user);
    }
}
