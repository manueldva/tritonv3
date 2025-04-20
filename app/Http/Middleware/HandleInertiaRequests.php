<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring; // Puede que no se use en share, pero se deja por si acaso
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy; // Asegúrate de que Ziggy esté instalado y configurado
use Illuminate\Support\Facades\Auth;
use App\Models\MenuItem; // Importar el modelo MenuItem

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // Si hay un usuario autenticado, cargar la relación 'empresa'
        if ($user) {
            $user->load('empresa'); // Carga eager load la relación
        }

        // *** Obtener y filtrar los ítems del menú desde la base de datos ***
        $menuItemsQuery = MenuItem::query()
            ->where('status', true) // Solo ítems activos
            ->orderBy('order'); // Ordenar por la columna 'order'

        // Filtrar ítems que solo son para administradores si el usuario no es admin
        if (!$user || !$user->is_admin) {
            $menuItemsQuery->where('is_admin_only', false);
        }

        // Obtener los ítems y seleccionar solo las columnas necesarias para el frontend
        // Asegúrate de que 'icon' y 'href' tengan los valores correctos (nombre del icono y nombre de la ruta)
        $activeMenuItems = $menuItemsQuery->get(['title', 'href', 'icon']);

        $shared = parent::share($request);

        // *** CONSTRUIR EL ARRAY FLASH EXPLICITAMENTE RECUPERANDO LAS CLAVES DE LA SESIÓN ***
        $flashMessages = [
            'success' => $request->session()->get('success'), // Obtener 'success' directamente
            'error' => $request->session()->get('error'),   // Obtener 'error' directamente
            // Añade aquí otras claves si usas ->with('warning', ...) o ->with('info', ...)
            // 'warning' => $request->session()->get('warning'),
            // 'info' => $request->session()->get('info'),
        ];

        // Filtrar para no compartir claves nulas si no hay mensaje (convierte null a no existir en el array)
        $flashMessages = array_filter($flashMessages);


        // *** Devolver el array combinado, compartiendo el array $flashMessages construido ***
        return array_merge($shared, [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'is_active' => $user->is_active,
                    'empresa_id' => $user->empresa_id,
                    'empresa' => $user->empresa ? [
                         'id' => $user->empresa->id,
                         'name' => $user->empresa->name,
                    ] : null,
                ] : null,
            ],
            'menuItems' => $activeMenuItems,

            // *** Compartir el array $flashMessages construido explicitamente ***
            'flash' => $flashMessages,

            // parent::share() ya suele incluir 'ziggy'
            // Si no fuera así, deberías añadirlo aquí:
            // 'ziggy' => (new Ziggy)->toArray(),
            // ... otras props personalizadas que necesites ...
        ]);

        // Asegúrate de que NO haya ningún otro 'return' después de este bloque.
    }
}