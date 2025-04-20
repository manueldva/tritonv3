<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Empresa; // Importa el modelo Empresa
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth; // *** Asegúrate de que Auth esté importado ***
use Illuminate\Support\Facades\Log;


class ManageuserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->where('empresa_id', Auth::user()->empresa_id);
        
        //$query->where('empresa_id', $userEmpresaId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
                  //->orWhere('document', 'like', "%{$search}%");
            });
        }
        //dd($query->toSql());
        return Inertia::render('manageusers/index', [
            'users' => $query->paginate(10)->withQueryString(),
            'search' => $search, // opcional, para que el input se mantenga
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('manageusers/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Log::info('Datos recibidos:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            
        ]);

        $data = $request->only(['name', 'email', 'is_active']);
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['password'] = Hash::make(123456789);

        $user = User::create($data);

        event(new Registered($user));

        return redirect()->route('manageusers.index')->with('success', ' Usuario creado con éxito. Contraseña tempora: 123456789');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user(); // Obtiene el usuario logueado
        $isAdmin = $currentUser && $currentUser->is_admin; // Verifica si es admin

        $empresas = [];
        // Si el usuario logueado es admin, obtenemos las empresas activas
        if ($isAdmin) {
            // Seleccionamos solo 'id' y 'name' para el selector
            $empresas = Empresa::where('status', true)->get(['id', 'name']);
        }

        // Cargamos la relación 'empresa' en el usuario si existe para mostrar el valor actual en el combo
         $user->load('empresa');


        return Inertia::render('manageusers/edit',[
            'user' => $user, // El usuario a editar
            'empresas' => $empresas, // La lista de empresas (vacía si no es admin)
            // No necesitamos pasar isAdmin explícitamente si ya se comparte globalmente auth.user
            // 'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Log::info('Datos recibidos:', $request->all());

        $currentUser = Auth::user(); // Obtiene el usuario logueado
        $isAdmin = $currentUser && $currentUser->is_admin; // Verifica si es admin

        $validationRules = [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'reset' => 'nullable|boolean',
            // Agregamos la validación para empresa_id SOLO si el usuario logueado es admin
            // 'required_if:isAdmin,true' significa que es requerido si 'isAdmin' en la request es true
            // 'exists:empresas,id' verifica que el ID exista en la tabla 'empresas'
            'empresa_id' => $isAdmin ? 'required|integer|exists:empresas,id' : 'nullable|integer',
             // Si no es admin, es solo nullable integer (para que la validación no falle si se envía, aunque no debería mostrarse el campo)
        ];

        // Si pasas 'isAdmin' en el formulario (no es recomendable, mejor usar Auth::user() en backend)
        // $validationRules = [ ... 'empresa_id' => 'required_if:isAdmin,true|integer|exists:empresas,id', 'isAdmin' => 'boolean' ];
        // Y validar 'isAdmin' aquí si lo envías del frontend

        $validatedData = $request->validate($validationRules);

        $userDataToUpdate = [
            'name' => $validatedData['name'],
            'is_active' => $validatedData['is_active'] ?? false,
        ];

        // Lógica condicional para actualizar empresa_id SOLO si el usuario logueado es admin
        if ($isAdmin && isset($validatedData['empresa_id'])) {
             $userDataToUpdate['empresa_id'] = $validatedData['empresa_id'];
        }

        // Lógica condicional para resetear la contraseña
        $passwordReset = false;
        if (isset($validatedData['reset']) && $validatedData['reset'] === true) {
             $userDataToUpdate['password'] = Hash::make('123456789');
             $passwordReset = true;
        }

        // Realizamos la actualización
        $user->update($userDataToUpdate);

        // Preparamos el mensaje flash
        $message = 'Usuario editado con éxito.';
        if ($passwordReset) {
            $message .= ' La contraseña ha sido reseteada a "123456789".';
        }

        // Redirigimos
        return redirect()->route('manageusers.index')->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    /*
    public function destroy(Request $request, Client $client) // Inyecta Request
    {
        // Verifica la autorización si es necesario (ej: Policy)
        // if (Auth::user()->cannot('delete', $client)) {
        //     abort(403);
        // }

        $client->delete();

        // Redirige a la ruta index, pasando los parámetros de la query string
        // actuales (como 'search', 'page', etc.) para mantener el estado de la tabla.
        return redirect()->route('clients.index', $request->query())
                    ->with('success', ' Cliente eliminado con éxito.');
    }*/
    public function destroy(Request $request, User $user) // <-- Inyecta Request
    {
        
        if ($user->id === Auth::user()->id || $user->isAdmin()) {
            // Si existen usuarios, no permitir la eliminación y redirigir con error
            return redirect()->route('manageusers.index')
                             ->with('error', ' No puede eliminar este usuario.');
        }

        $user->delete();
        // Pasa los query parameters actuales (search, page, etc.) a la redirección
        return redirect()->route('manageusers.index', $request->query())
                    ->with('success', ' Usuario eliminado con éxito.');
    }
}
