<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::query();

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
            'lastname' => 'required|string|max:255',
            'document' => 'required|string|max:20',
            
        ]);
        Client::create($request->all());
        return redirect()->route('clients.index');

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
        
        Log::info('Datos recibidos:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'document' => 'required|string|max:20',
        ]);
        $client->update($request->all());
        /*$client->update([
            'name' => $request->input('name'),
            'lastname' => $request->input('lastname'),
            'document' => $request->input('document'),
            'status' => $request->boolean('status'), // <- esta línea es clave
        ]);*/
        return redirect()->route('clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return Inertia::render('clients/index', [
            'clients' =>Client::paginate(10),
            //'clients' => Client::select('id','name','lastname')->paginate(10),
        ]);
    }
}
