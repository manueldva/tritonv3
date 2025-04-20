<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
// *** Importar Head, usePage, router, Link, y computed ***
import { Head, usePage, router, Link } from '@inertiajs/vue3'; // Importar Link y usePage
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// *** Importar el componente InputError ***
import InputError from '@/components/InputError.vue'; // Asegúrate de que esta ruta sea correcta
import { ref, onMounted, computed } from 'vue'; // Necesitas ref, onMounted, computed
import type { User } from '@/types';

// Breadcrumbs
type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
 { title: 'Usuarios', href: '/manageusers' },
 { title: 'Editar', href: '#' }
];

// users desde props
const page = usePage(); // Acceder a page props aquí
const user = page.props.user as User;

// Formulario reactivo
const form = ref<Partial<{
 name: string;
 email: string;
 is_active: boolean;
 reset: boolean;
}>>({
 name: '',
 email: '',
 is_active: false,
 reset: false
});

onMounted(() => {
 form.value = {
  name: user.name ?? '',
  email: user.email ?? '',
  is_active: !!user.is_active, // Convertir a booleano
  reset: false // Convertir a booleano
 };
});

// En una página de edición, normalmente no necesitas resetear el formulario a valores vacíos después de guardar
// const resetForm = () => {
//  form.value = {
//   name: '',
//   lastname: '',
//   document: '',
//   status: false
//  };
// };

const submit = () => {
 router.put(`/manageusers/${user.id}`, form.value, {
  // onSuccess para una página de edición podría redirigir o mostrar un mensaje, no suele resetear el formulario
  // onSuccess: resetForm // Remover o cambiar esto
      /*onSuccess: () => {
          // Opcional: Redirigir al índice después de una actualización exitosa
          router.visit('/clients');
          // Opcional: El mensaje flash de éxito se manejaría con un redirect en el controlador
      },*/
      // onError no es manejado automáticamente por router.put con ref,
      // pero Inertia igualmente llenará page.props.errors en la redirección de vuelta
      // Puedes añadir un callback onError si necesitas hacer algo adicional con los errores
      // onError: (errors) => { console.error('Validation errors:', errors); } // Opcional: para depuración
 });
};

// *** Acceder a los errores de validación desde los props de la página ***
// page ya está definido arriba
const validationErrors = computed(() => page.props.errors || {});


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
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
