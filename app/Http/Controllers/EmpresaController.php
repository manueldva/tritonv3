<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log; // Si quieres usar Log para depurar
use Illuminate\Support\Facades\Auth; 

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        if(Auth::user()->isAdmin()) {
            $query = Empresa::query();
        }else{
            $query = Empresa::query()->where('id', Auth::user()->empresa_id);
        }
       

        // Similar a clientes, permite buscar por nombre, ruc, email, etc.
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Opcional: ordenar por nombre por defecto
        $query->orderBy('name');


        return Inertia::render('empresas/index', [
            'empresas' => $query->paginate(10)->withQueryString(),
            'search' => $search, // para mantener el valor en el input de búsqueda
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('empresas/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::info('Datos recibidos para crear empresa:', $request->all()); // Para depurar

        $request->validate([
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20', // nullable si no es obligatorio
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            // 'status' - no es necesario validar si siempre viene true por defecto o no está en el form de create
        ]);

        Empresa::create($request->all());

        // Redirigir al índice con un mensaje de éxito si quieres (requiere manejo de flash messages en Vue)
        return redirect()->route('empresas.index')->with('success', ' Empresa creada con exito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // No implementado en el ejemplo de cliente, puedes añadirlo si necesitas ver detalles de una empresa
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa) // Usando Route Model Binding
    {
        return Inertia::render('empresas/edit', [
            'empresa' => $empresa,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa) // Usando Route Model Binding
    {
        // Log::info('Datos recibidos para actualizar empresa:', $request->all()); // Para depurar

        $request->validate([
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'status' => 'boolean', // Validar si viene como booleano
        ]);

        // Asegúrate de que $request->all() incluye 'status' o manéjalo explícitamente
        // Si el checkbox no se marca, 'status' no estará en $request->all() por defecto.
        // Una forma segura es:
        $empresa->update($request->only(['name', 'ruc', 'address', 'phone', 'email']) + ['status' => $request->boolean('status')]);


        return redirect()->route('empresas.index')->with('success', ' Empresa editada con exito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa) // Usando Route Model Binding
    {
        
        
        // --- Verificación de usuarios asociados ---
        // Llama a la relación 'users()' y verifica si existe algún registro relacionado
        if ($empresa->users()->exists()) {
            // Si existen usuarios, no permitir la eliminación y redirigir con error
            return redirect()->route('empresas.index')
                             ->with('error', ' No se puede eliminar la empresa porque tiene usuarios asociados.');
        }
        //

        try {

            $empresa->delete();
             // Redirigir al índice. Inertia recargará los datos.
            return redirect()->route('empresas.index')->with('success', ' Se elimino correctamente.');
        } catch (\Exception $e) {
             // Log::error('Error al eliminar empresa:', ['error' => $e->getMessage(), 'empresa_id' => $empresa->id]);
            // Aquí puedes manejar el error, por ejemplo, si hay registros relacionados
            // y la base de datos impide la eliminación (Foreign Key Constraint).
            // Podrías redirigir de vuelta con un error flash message.
             return redirect()->route('empresas.index')->with('error', 'No se pudo eliminar la empresa. Podría tener datos asociados.');
        }


    }
}