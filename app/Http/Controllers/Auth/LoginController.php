<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuthService;
use App\Services\JwtService;
use Inertia\Inertia;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    protected AuthService $service;
    protected JwtService $jwtService;

    public function __construct(AuthService $service, JwtService $jwtService)
    {
        $this->service = $service;
        $this->jwtService = $jwtService;
    }

    // Unificamos showLogin e showLoginForm aqui
    public function showLogin()
    {
        if (auth()->check()) {
            if (auth()->user()->isStaff()) {
                return redirect()->intended('/dashboard');
            }
            if (auth()->user()->isClient()) {
                return redirect()->route('store.index');
            }
        }

        return Inertia::render('Auth/Login', [
            'userIp' => request()->ip(),
            'status' => session('status'),
        ]);
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if ($this->service->login($credentials, $request->boolean('remember'))) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou conta bloqueada.',
        ]);
    }

    // API Login - Retorna JSON com token JWT
    public function apiLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conta inativa.',
                ], 401);
            }

            $token = $this->jwtService->generateToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciais inválidas.',
        ], 401);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->service->logout($request);
        return redirect('/');
    }

    // API Logout - Retorna JSON
    public function apiLogout(Request $request): JsonResponse
    {
        auth()->logout();
        
        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
        ], 200);
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->service->sendResetLink($data['email']);
            return back()->with('success', 'Link enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Erro no provedor de e-mail: ' . $e->getMessage()
            ]);
        }
    }
    
    // Remova o método showLoginForm() duplicado se ele existir no final do arquivo
}