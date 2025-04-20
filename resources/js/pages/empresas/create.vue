<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';

// Breadcrumbs
type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Empresas', href: '/empresas' }, // Actualizado
    { title: 'Crear', href: '#' }
];

// Formulario reactivo con los campos de Empresa
const form = ref<Partial<{
    name: string;
    ruc: string | null;
    address: string | null;
    phone: string | null;
    email: string | null;
    status: boolean;
}>>({
    name: '',
    ruc: null, // Usar null para campos opcionales/nullable
    address: null,
    phone: null,
    email: null,
    status: true, // Generalmente una empresa nueva está activa por defecto
});

const resetForm = () => {
    form.value = {
        name: '',
        ruc: null,
        address: null,
        phone: null,
        email: null,
        status: true
    };
};

const submit = () => {
     // Ruta de envío actualizada
    router.post('/empresas', form.value, {
         onSuccess: () => {
             // Opcional: mostrar mensaje de éxito
             // console.log('Empresa creada con éxito');
             resetForm(); // Si quieres limpiar el formulario después de guardar
             // Opcional: redirigir al índice después de guardar exitosamente (si no lo hace ya el controlador)
             // router.visit('/empresas');
         },
         onError: (errors) => {
             console.error('Error al crear empresa:', errors);
             // Manejar y mostrar errores de validación si es necesario
         }
    });
};
</script>

<template>
    <Head title="Crear Empresa" /> <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 justify-center p-6">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-md p-8 space-y-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Empresa</h1> <form @submit.prevent="submit" class="space-y-6">
                    <div
                         v-for="(label, key) in { name: 'Nombre', ruc: 'RUC', address: 'Dirección', phone: 'Teléfono', email: 'Email', status: 'Estado' }"
                         :key="key"
                          class='space-y-2'
                    >
                        <Label :for="key" class="text-gray-700 dark:text-gray-300">{{ label }}</Label>

                        <Input
                            v-if="key !== 'status'"
                            :id="key"
                            v-model="form[key]"
                            :type="key === 'email' ? 'email' : (key === 'phone' ? 'number' : 'text')" :placeholder="label"
                            :required="key === 'name'" class="w-full"
                        />

                        <div v-else class="flex items-center gap-3">
                             <input
                                :id="key"
                                 v-model="form.status"
                                 type="checkbox"
                                 class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                            />
                            <span class="text-sm text-gray-600 dark:text-gray-300">Activo</span>
                        </div>
                    </div>


                    <div class="flex justify-end gap-4 pt-4">
                        <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">
                            Guardar
                        </Button>
                        <Button as="a" href="/empresas" variant="outline"> Cancelar
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>