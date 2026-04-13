<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Models\User;
use Inertia\Inertia;
use Modules\User\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Modules\User\Http\Requests\User\StoreUserRequest;
use Modules\User\Http\Requests\User\UpdateUserRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->service->list(auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return Inertia::render('Users/Index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = $this->service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'data' => $user,
            ], 201);
        }

        return redirect()->route('users.index')
            ->with('message', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $this->service->update($user, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado!',
                'data' => $user->refresh(),
            ], 200);
        }

        return redirect()->route('users.index')
            ->with('message', 'Usuário atualizado!');
    }

    public function toggleStatus(User $user, Request $request)
    {
        $this->authorize('toggleStatus', $user);

        $this->service->toggleStatus($user, auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado!',
                'data' => $user->refresh(),
            ], 200);
        }

        return back()->with('message', 'Status atualizado!');
    }

    public function resetPassword(User $user, Request $request)
    {
        $this->authorize('resetPassword', $user);

        $this->service->resetPassword($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Senha resetada para: Mudar@123',
                'data' => $user->refresh(),
            ], 200);
        }

        return back()->with('message', 'Senha resetada para: Mudar@123');
    }

    public function destroy(User $user, Request $request)
    {
        $this->authorize('delete', $user);

        $this->service->delete($user, auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído!',
            ], 204);
        }

        return redirect()->route('users.index')
            ->with('message', 'Usuário excluído!');
    }
}
