<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use App\Models\Empresa; // Importa el modelo Empresa
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        // Obtener solo empresas activas para el registro
        $empresas = Empresa::where('status', true)->orderBy('name')->get(['id', 'name']);

        // Renderizar la vista de registro y pasar las empresas
        // Asumiendo que tu vista de registro es resources/js/pages/Auth/Register.vue
        return Inertia::render('auth/Register', [
            'empresas' => $empresas,
        ]);

        //return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Datos recibidos:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'empresa_id' => 'required',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'empresa_id' => $request->empresa_id,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
