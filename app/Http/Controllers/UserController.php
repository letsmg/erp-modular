<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        
        $query = User::orderBy('name');

        // REGRA: Usuário padrão não vê Administradores
        if ($currentUser->access_level !== 1) {
            $query->where('access_level', 0);
        }

        return Inertia::render('Users/Index', [
            'users' => $query->get()
        ]);
    }

    // Método para Ativar/Desativar
    public function toggleStatus(User $user)
    {
        // SEGURANÇA: Não permite desativar a si mesmo
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back()->with('message', 'Status atualizado!');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'access_level' => 'required|integer',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);
        return redirect()->back()->with('message', 'Usuário criado!');
    }



    public function edit(User $user)
    {
        // SEGURANÇA: Se não for admin e tentar editar outro ID, bloqueia
        if (auth()->user()->access_level !== 1 && auth()->id() !== $user->id) {
            abort(403, 'Você só pode editar seu próprio perfil.');
        }

        return Inertia::render('Users/Edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            // 'nullable' permite deixar em branco
            // 'confirmed' exige que password == password_confirmation
            'password' => 'nullable|string|min:8|confirmed', 
        ];

        // ... (restante da lógica de Admin que fizemos antes)

        $data = $request->validate($rules);

        // Só criptografa e atualiza a senha se ela for preenchida
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // Remove do array para não zerar a senha no DB
        }

        $user->update($data);

        return redirect()->route('users.index')->with('message', 'Usuário atualizado com sucesso!');
    }




    // O "Esqueci minha senha" versão Admin
    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('Mudar@123') // Senha padrão temporária
        ]);

        return redirect()->back()->with('message', 'Senha resetada para: Mudar@123');
    }



    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $user->delete();
        return redirect()->back();
    }
}