<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, CirclePlus, Check, X } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';

interface UserPageProps extends SharedData {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    search?: string;
}

const { props } = usePage<UserPageProps>();
const users = computed(() => props.users);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Usuarios', href: '/manageusers' }];

const isModalOpen = ref(false);
const userToDelete = ref<number | null>(null);
const searchQuery = ref(props.search || ''); // Ref para el input de búsqueda
const searchInput = ref<HTMLInputElement | null>(null); // Ref para el input element

const openModal = (id: number) => {
    userToDelete.value = id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    userToDelete.value = null;
};

/*
const deleteClient = async () => {
    if (clientToDelete.value === null) return;

    try {
        // Usa router.delete. Inertia manejará la redirección del backend.
        await router.delete(`/clients/${clientToDelete.value}`, {
            preserveScroll: true, // Mantiene la posición de scroll
            // preserveState: false, // Usualmente no necesitas preserveState aquí, 
                                    // ya que quieres que los props (lista de clientes) se refresquen.
                                    // Inertia por defecto (false) ya busca los nuevos props.
            onSuccess: () => {
                closeModal(); // Solo cierra el modal aquí
                // NO hagas router.visit aquí. Laravel ya redirige.
                // El mensaje flash y la lista actualizada vendrán con la
                // actualización automática de Inertia tras la redirección.
            },
            onError: (errors) => {
                console.error('Error al eliminar el cliente:', errors);
                // Considera mostrar un mensaje de error al usuario aquí
            },
        });
    } catch (error) {
        console.error('Error inesperado al eliminar el cliente:', error);
        // Considera mostrar un mensaje de error genérico
    }
};
*/
const deleteUser = async () => {
    if (userToDelete.value === null) return;

    router.delete(`/manageusers/${userToDelete.value}`, {
        preserveScroll: true,
        preserveState: false, // <-- Clave: Fuerza a Inertia a recargar los props
        onSuccess: () => {
            closeModal();
            // No hay router.visit aquí. Inertia debe manejar la recarga
            // debido a preserveState: false y la redirección del backend.
            // El mensaje flash y la tabla actualizada deberían llegar ahora.
        },
        onError: (errors) => {
            console.error('Error al eliminar el usuario:', errors);
            // Considera mostrar un mensaje de error al usuario
        },
    });
};

/*
const performSearch = () => {
    router.get('/clients', { search: searchQuery.value }, {
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            // Enfocar el input de búsqueda después de la búsqueda
            if (searchInput.value) {
                searchInput.value.focus();
            }
        },
    });
};
*/
const performSearch = () => {
    router.get('/manageusers', { search: searchQuery.value }, {
        // NO uses preserveState: true aquí para la búsqueda principal
        preserveScroll: true, // Bueno para mantener la posición
        replace: true,       // Bueno para no llenar el historial con cada búsqueda
    });
};

// Enfocar el input de búsqueda al montar el componente (opcional)
onMounted(() => {
    if (searchInput.value && !props.search) {
        searchInput.value.focus();
    }
});
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Usuarios</h1>
                <Button as-child size="sm" class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Link href="/manageusers/create"><CirclePlus class="mr-1" /> Crear Usuario</Link>
                </Button>
            </div>

            <div class="flex">
                <input
                    ref="searchInput" 
                    v-model="searchQuery"
                    @keyup.enter="performSearch" 
                    type="text"
                    placeholder="Buscar por nombre o documento..."
                    class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>

            <div class="overflow-auto rounded-lg border border-gray-200 shadow-sm">
                <Table>
                    <TableCaption class="text-gray-500">Listado de usuarios</TableCaption>
                    <TableHeader>
                        <TableRow class="bg-gray-100 dark:bg-gray-800">
                            <TableHead>Nombre</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Activo</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <TableCell class="font-semibold">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <Check v-if="user.is_active" class="text-green-500" />
                                <X v-else class="text-red-500" />
                            </TableCell>
                            <TableCell class="flex justify-center gap-2 py-2">
                                <Button  v-if="!user.is_admin" as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                                    <Link :href="`/manageusers/${user.id}/edit`"><Pencil /></Link>
                                </Button>
                                <Button  v-if="!user.is_admin"  size="sm" class="bg-rose-500 text-white hover:bg-rose-700" @click="openModal(user.id)">
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center gap-4 mt-4">
                <Button
                    v-if="users.prev_page_url"
                    @click="router.visit(users.prev_page_url)"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    ⬅ Anterior
                </Button>
                <Button
                    v-if="users.next_page_url"
                    @click="router.visit(users.next_page_url)"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    Siguiente ➡
                </Button>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm w-full">
                    <h3 class="text-lg font-bold text-center text-gray-800 dark:text-white mb-4">Confirmar Eliminación</h3>
                    <p class="text-center text-gray-600 dark:text-gray-300 mb-6">¿Estás seguro que deseas eliminar este usuario?</p>
                    <div class="flex justify-center gap-4">
                        <Button @click="deleteUser" class="bg-red-600 text-white hover:bg-red-700">Confirmar</Button>
                        <Button @click="closeModal" class="bg-gray-500 text-white hover:bg-gray-600">Cancelar</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>