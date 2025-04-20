<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
// Asegúrate de tener un tipo Empresa definido en types.ts
import { Empresa, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, CirclePlus, Check, X } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';

// Define el tipo para las props de la página
interface EmpresaPageProps extends SharedData {
    empresas: { // ¡Cambiado a empresas!
        data: Empresa[]; // Usa el tipo Empresa
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    search?: string;
}

// Usa el tipo correcto para usePage
const { props } = usePage<EmpresaPageProps>();
const empresas = computed(() => props.empresas); // ¡Cambiado a empresas!

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Empresas', href: '/empresas' }]; // Actualizado

const isModalOpen = ref(false);
const empresaToDelete = ref<number | null>(null); // Actualizado
const searchQuery = ref(props.search || '');
const searchInput = ref<HTMLInputElement | null>(null);

const openModal = (id: number) => {
    empresaToDelete.value = id; // Actualizado
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    empresaToDelete.value = null; // Actualizado
};

/*
const deleteEmpresa = async () => {
    if (empresaToDelete.value === null) return;

    try {
        await router.delete(`/empresas/${empresaToDelete.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                // ¡Añade/descomenta esta línea para forzar la recarga!
                router.visit('/empresas', { replace: true });
            },
             onError: (errors) => {
                console.error('Error al eliminar la empresa:', errors);
                // Mostrar mensaje de error si el controlador envía uno (ej: FK constraint)
                // Esto asume que usas flash messages en tu AppLayout o en otro lugar
                if (usePage().props.flash?.error) {
                     alert(usePage().props.flash.error);
                 } else {
                     alert('Ocurrió un error al eliminar la empresa.'); // Mensaje genérico si no hay flash
                 }
            },
        });
    } catch (error) {
        console.error('Error general al eliminar la empresa:', error);
        alert('Ocurrió un error inesperado.'); // Mensaje para errores de red, etc.
    }
};
*/

const deleteEmpresa = async () => {
    if (empresaToDelete.value === null) return;

    router.delete(`/empresas/${empresaToDelete.value}`, {
        preserveScroll: true,
        preserveState: false, // <-- Clave: Fuerza a Inertia a recargar los props
        onSuccess: () => {
            closeModal();
            // No hay router.visit aquí. Inertia debe manejar la recarga
            // debido a preserveState: false y la redirección del backend.
            // El mensaje flash y la tabla actualizada deberían llegar ahora.
        },
        onError: (errors) => {
            console.error('Error al eliminar el empresa:', errors);
            // Considera mostrar un mensaje de error al usuario
        },
    });
};

const performSearch = () => {
     // Ruta actualizada
    router.get('/empresas', { search: searchQuery.value }, {
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
             if (searchInput.value) {
                 searchInput.value.focus();
             }
         },
    });
};

// Enfocar el input de búsqueda al montar el componente (opcional)
onMounted(() => {
     // Comprueba si el input existe y si no hay búsqueda previa para enfocar
    if (searchInput.value && !props.search) {
         searchInput.value.focus();
    }
});
</script>

<template>
    <Head title="Empresas" /> <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Empresas</h1> <Button as-child size="sm" class="bg-indigo-600 text-white hover:bg-indigo-700">
                     <Link href="/empresas/create"><CirclePlus class="mr-1" /> Crear Empresa</Link> </Button>
            </div>

            <div class="flex">
                <input
                    ref="searchInput"
                    v-model="searchQuery"
                    @keyup.enter="performSearch"
                    type="text"
                    placeholder="Buscar por nombre, RUC o email..." class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>

            <div class="overflow-auto rounded-lg border border-gray-200 shadow-sm">
                <Table>
                     <TableCaption class="text-gray-500">Listado de empresas</TableCaption> <TableHeader>
                         <TableRow class="bg-gray-100 dark:bg-gray-800">
                            <TableHead>Nombre</TableHead> <TableHead>RUC</TableHead>    <TableHead>Email</TableHead>  <TableHead>Estado</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="empresa in empresas.data" :key="empresa.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <TableCell class="font-semibold">{{ empresa.name }}</TableCell> <TableCell>{{ empresa.ruc }}</TableCell>       <TableCell>{{ empresa.email }}</TableCell>     <TableCell>
                                <Check v-if="empresa.status" class="text-green-500" /> <X v-else class="text-red-500" /> </TableCell>
                            <TableCell class="flex justify-center gap-2 py-2">
                                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                                     <Link :href="`/empresas/${empresa.id}/edit`"><Pencil /></Link> </Button>
                                <Button size="sm" class="bg-rose-500 text-white hover:bg-rose-700" @click="openModal(empresa.id)"> <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

             <div class="flex justify-center gap-4 mt-4">
                <Button
                    v-if="empresas.prev_page_url" @click="router.visit(empresas.prev_page_url)" class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    ⬅ Anterior
                </Button>
                <Button
                    v-if="empresas.next_page_url" @click="router.visit(empresas.next_page_url)" class="bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    Siguiente ➡
                </Button>
            </div>


            <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm w-full">
                    <h3 class="text-lg font-bold text-center text-gray-800 dark:text-white mb-4">Confirmar Eliminación</h3> <p class="text-center text-gray-600 dark:text-gray-300 mb-6">¿Estás seguro que deseas eliminar esta empresa?</p> <div class="flex justify-center gap-4">
                        <Button @click="deleteEmpresa" class="bg-red-600 text-white hover:bg-red-700">Confirmar</Button> <Button @click="closeModal" class="bg-gray-500 text-white hover:bg-gray-600">Cancelar</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>