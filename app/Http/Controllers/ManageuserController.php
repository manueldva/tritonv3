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
        return Inertia::render('manageusers/edit',[
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Log::info('Datos recibidos:', $request->all()); // Útil para depurar si es necesario

        // Validar solo los campos que esperamos recibir y procesar
        // Aseguramos que 'name' sea requerido
        // 'is_active' es un booleano, puede ser null si no se envía (ej. si el campo está deshabilitado en frontend)
        // 'reset' es un booleano, también puede ser null si no se envía
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean', // is_active debe ser booleano
            'reset' => 'nullable|boolean',    // reset debe ser booleano si se envía
        ]);

        // Preparamos los datos para actualizar el usuario.
        // Solo incluimos los campos que queremos modificar directamente.
        $userDataToUpdate = [
            'name' => $validatedData['name'],
            // Usamos el valor validado para 'is_active', por defecto a false si no se envió y es nullable
            'is_active' => $validatedData['is_active'] ?? false,
        ];

        // Lógica condicional para resetear la contraseña
        $passwordReset = false;
        // Comprobamos si 'reset' existe en los datos validados Y si su valor es true
        if (isset($validatedData['reset']) && $validatedData['reset'] === true) {
             // Si reset es true, añadimos el campo 'password' a los datos a actualizar
             // Asegúrate de HASHEAR la contraseña antes de guardarla
             $userDataToUpdate['password'] = Hash::make('123456789');
             $passwordReset = true; // Marcamos que la contraseña fue reseteada para el mensaje flash
        }

        // Realizamos la actualización usando los datos preparados
        // Esto es más seguro que $user->update($request->all()) porque evita la asignación masiva de campos no deseados
        $user->update($userDataToUpdate);

        // Preparamos el mensaje flash
        $message = ' Usuario editado con éxito.';
        if ($passwordReset) {
            // Añadimos información al mensaje si la contraseña fue reseteada
            $message .= ' La contraseña ha sido reseteada a "123456789".';
        }

        // Redirigimos a la página de índice con el mensaje flash
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
