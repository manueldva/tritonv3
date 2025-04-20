<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
// *** Importar Head, usePage, router, Link, y computed ***
import { Head, usePage, router, Link } from '@inertiajs/vue3'; // Importar Link y usePage
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; // Asumimos que sigues usando este para name/email
import { Label } from '@/components/ui/label';
import {  Select,  SelectContent,  SelectItem,  SelectTrigger,  SelectValue,} from '@/components/ui/select'; // Esta ruta ahora será válida
// *** Importar el componente InputError ***
import InputError from '@/components/InputError.vue'; // Asegúrate de que esta ruta sea correcta
import { ref, onMounted, computed } from 'vue'; // Necesitas ref, onMounted, computed
import type { User, SharedData } from '@/types'; // Asegúrate de importar SharedData también

// Define la interfaz para las opciones del selector de Empresas
interface EmpresaOption {
    id: number;
    name: string;
}

// Define la interfaz de props para esta página, incluyendo 'user', 'empresas', 'auth', 'errors'
interface EditUserPageProps extends SharedData {
    user: User; // El usuario a editar (ahora debería incluir empresa_id)
    empresas: EmpresaOption[]; // Array de empresas activas (puede estar vacío si no es admin)
    auth: { user: User | null }; // Información del usuario logueado (para saber si es admin)
    errors: Record<string, string>; // Errores de validación
    // Agrega otras props compartidas si las usas en esta vista
}

// Usar la interfaz con usePage
const { props } = usePage<EditUserPageProps>();

// Acceder a las props de forma reactiva usando computed
const user = computed(() => props.user); // El usuario a editar
const empresas = computed(() => props.empresas); // Lista de empresas
const currentUser = computed(() => props.auth?.user); // Usuario logueado

// Breadcrumbs (se mantienen igual)
type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/manageusers' },
    { title: 'Editar', href: '#' }
];

// Formulario reactivo - Incluye empresa_id
const form = ref({
    name: '',
    email: '', // Aunque esté deshabilitado visualmente, es bueno tenerlo en el formulario
    is_active: false,
    reset: false,
    empresa_id: null as number | null, // Añade empresa_id, inicializado a null
});

// Propiedad computada para saber si el usuario logueado es admin
const isCurrentUserAdmin = computed(() => currentUser.value?.is_admin ?? false); // Por defecto false si currentUser es null

// Populate form on mount
onMounted(() => {
    // Usar user.value porque es un computed ref
    form.value = {
        name: user.value.name ?? '',
        email: user.value.email ?? '',
        is_active: !!user.value.is_active, // Asegura que sea booleano
        reset: false, // Inicializa reset a false
        empresa_id: user.value.empresa_id ?? null, // Inicializa empresa_id con el valor actual del usuario
    };
});

// Submit function (se mantiene igual, router.put envía form.value completo)
const submit = () => {
     // Inertia automáticamente incluye form.value en la request body para router.put
    router.put(`/manageusers/${user.value.id}`, form.value, {
        // onSuccess/onError se manejan automáticamente con las redirecciones de Inertia
        // Puedes añadir callbacks si necesitas lógica UI específica (ej: mostrar notificación toast)
        // onError: (errors) => { console.error('Validation errors:', errors); } // Ejemplo de onError para depurar
    });
};

// Acceder a los errores de validación desde props
const validationErrors = computed(() => props.errors);

</script>

<template>
    <Head title="Editar Usuario" /> <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 justify-center p-6">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-md p-8 space-y-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Editar Usuario</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <div
                        v-for="(label, key) in { name: 'Nombre', email: 'Email', is_active: 'Estado', reset: 'Resetear' }"
                        :key="key"
                        class='space-y-2'
                    >
                         <Label :for="key" class="text-gray-700 dark:text-gray-300">{{ label }}</Label>

                        <template v-if="key === 'name' || key === 'email'">
                             <Input
                                :id="key"
                                v-model="form[key]"
                                :type="key === 'email' ? 'email' : 'text'"
                                :disabled="key === 'email' ? true : false" :placeholder="label"
                                :required="key === 'name'" class="w-full"
                             />
                             <InputError :message="validationErrors[key]" class="mt-2" />
                        </template>

                        <template v-else-if="key === 'is_active' || key === 'reset'">
                             <div class="flex items-center gap-3">
                                 <input
                                    :id="key"
                                    v-model="form[key]" type="checkbox"
                                    class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                />
                                <span v-if="key === 'is_active'" class="text-sm text-gray-600 dark:text-gray-300">Usuario activo</span>
                                <span v-if="key === 'reset'" class="text-sm text-gray-600 dark:text-gray-300">Solicitar reseteo de contraseña</span>
                             </div>
                             <InputError :message="validationErrors[key]" class="mt-2" />
                        </template>

                    </div>

                    <div v-if="isCurrentUserAdmin" class="space-y-2">
                         <Label for="empresa_id" class="text-gray-700 dark:text-gray-300">Empresa</Label>
                         
                         <Select v-model="form.empresa_id">
                            <SelectTrigger id="empresa_id" :tabindex="2.5" class="w-full">
                                <SelectValue placeholder="-- Selecciona una empresa --" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="empresa in props.empresas"
                                    :key="empresa.id"
                                    :value="empresa.id"
                                >
                                    {{ empresa.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                         
                         <InputError :message="validationErrors.empresa_id" class="mt-2" />
                    </div>


                    <div class="flex justify-end gap-4 pt-4">
                        <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700" :disabled="form.processing">
                            Guardar
                        </Button>
                        <Link href="/manageusers">
                            <Button variant="outline">Cancelar</Button>
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>