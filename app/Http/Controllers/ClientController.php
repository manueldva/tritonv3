<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; // *** Asegúrate de que Auth esté importado ***
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::query()->where('empresa_id', Auth::user()->empresa_id);
        
        //$query->where('empresa_id', $userEmpresaId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        //dd($query->toSql());
        return Inertia::render('clients/index', [
            'clients' => $query->paginate(10)->withQueryString(),
            'search' => $search, // opcional, para que el input se mantenga
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('clients/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Log::info('Datos recibidos:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255', // Ahora permite null. Si no es null, debe ser string y max:255
            'document' => 'nullable|integer',       // Ahora permite null. Si no es null, debe ser un integer.
            
        ]);

        $data = $request->only(['name', 'lastname', 'document', 'status']);
        $data['empresa_id'] = Auth::user()->empresa_id;

        Client::create($data);
        return redirect()->route('clients.index')->with('success', ' Cliente creado con éxito.');

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
    public function edit(Client $client)
    {
        return Inertia::render('clients/edit',[
            'client' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        
        //Log::info('Datos recibidos:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255', // Ahora permite null. Si no es null, debe ser string y max:255
            'document' => 'nullable|integer',       // Ahora permite null. Si no es null, debe ser un integer.
        ]);
        $client->update($request->all());
        /*$client->update([
            'name' => $request->input('name'),
            'lastname' => $request->input('lastname'),
            'document' => $request->input('document'),
            'status' => $request->boolean('status'), // <- esta línea es clave
        ]);*/
        return redirect()->route('clients.index')->with('success', ' Cliente editado con éxito.');
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
    public function destroy(Request $request, Client $client) // <-- Inyecta Request
    {
        $client->delete();
        // Pasa los query parameters actuales (search, page, etc.) a la redirección
        return redirect()->route('clients.index', $request->query())
                    ->with('success', ' Cliente eliminado con éxito.');
    }
}
