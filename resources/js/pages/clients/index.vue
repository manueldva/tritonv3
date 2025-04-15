<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Client, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, CirclePlus, Check, X } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';

interface ClientPageProps extends SharedData {
    clients: {
        data: Client[];
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    search?: string;
}

const { props } = usePage<ClientPageProps>();
const clients = computed(() => props.clients);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Clients', href: '/clients' }];

const isModalOpen = ref(false);
const clientToDelete = ref<number | null>(null);
const searchQuery = ref(props.search || ''); // Ref para el input de búsqueda
const searchInput = ref<HTMLInputElement | null>(null); // Ref para el input element

const openModal = (id: number) => {
    clientToDelete.value = id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    clientToDelete.value = null;
};

const deleteClient = async () => {
    if (clientToDelete.value === null) return;

    try {
        await router.delete(`/clients/${clientToDelete.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                router.visit('/clients', { replace: true });
            },
            onError: (errors) => {
                console.error('Error al eliminar el cliente:', errors);
            },
        });
    } catch (error) {
        console.error('Error al eliminar el cliente:', error);
    }
};

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

// Enfocar el input de búsqueda al montar el componente (opcional)
onMounted(() => {
    if (searchInput.value && !props.search) {
        searchInput.value.focus();
    }
});
</script>

<template>
    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Clientes</h1>
                <Button as-child size="sm" class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Link href="/clients/create"><CirclePlus class="mr-1" /> Crear Cliente</Link>
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
                    <TableCaption class="text-gray-500">Listado de clientes</TableCaption>
                    <TableHeader>
                        <TableRow class="bg-gray-100 dark:bg-gray-800">
                            <TableHead>Nombre</TableHead>
                            <TableHead>Apellido</TableHead>
                            <TableHead>Documento</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="client in clients.data"
                            :key="client.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <TableCell class="font-semibold">{{ client.name }}</TableCell>
                            <TableCell>{{ client.lastname }}</TableCell>
                            <TableCell>{{ client.document }}</TableCell>
                            <TableCell>
                                <Check v-if="client.status" class="text-green-500" />
                                <X v-else class="text-red-500" />
                            </TableCell>
                            <TableCell class="flex justify-center gap-2 py-2">
                                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                                    <Link :href="`/clients/${client.id}/edit`"><Pencil /></Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 text-white hover:bg-rose-700" @click="openModal(client.id)">
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center gap-4 mt-4">
                <Button
                    v-if="clients.prev_page_url"
                    @click="router.visit(clients.prev_page_url)"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    ⬅ Anterior
                </Button>
                <Button
                    v-if="clients.next_page_url"
                    @click="router.visit(clients.next_page_url)"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    Siguiente ➡
                </Button>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm w-full">
                    <h3 class="text-lg font-bold text-center text-gray-800 dark:text-white mb-4">Confirmar Eliminación</h3>
                    <p class="text-center text-gray-600 dark:text-gray-300 mb-6">¿Estás seguro que deseas eliminar este cliente?</p>
                    <div class="flex justify-center gap-4">
                        <Button @click="deleteClient" class="bg-red-600 text-white hover:bg-red-700">Confirmar</Button>
                        <Button @click="closeModal" class="bg-gray-500 text-white hover:bg-gray-600">Cancelar</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>