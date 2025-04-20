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
import type { Client } from '@/types';

// Breadcrumbs
type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
 { title: 'Clients', href: '/clients' },
 { title: 'Editar', href: '#' }
];

// Cliente desde props
const page = usePage(); // Acceder a page props aquí
const client = page.props.client as Client;

// Formulario reactivo
const form = ref<Partial<{
 name: string;
 lastname: string;
 document: string;
 status: boolean;
}>>({
 name: '',
 lastname: '',
 document: '',
 status: false
});

onMounted(() => {
 form.value = {
  name: client.name ?? '',
  lastname: client.lastname ?? '',
  document: client.document ?? '', // Asegúrate de que esto maneje null/undefined correctamente si document es nullable
  status: !!client.status // Convertir a booleano
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
 router.put(`/clients/${client.id}`, form.value, {
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
 <Head title="Editar Cliente" />
 <AppLayout :breadcrumbs="breadcrumbs">
  <div class="flex flex-1 justify-center p-6">
   <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-md p-8 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Editar Cliente</h1>

    <form @submit.prevent="submit" class="space-y-6">
     <div
      v-for="(label, key) in { name: 'Nombre', lastname: 'Apellido', document: 'Documento', status: 'Estado' }"
      :key="key"
       class='space-y-2'
     >
      <Label :for="key" class="text-gray-700 dark:text-gray-300">{{ label }}</Label>

      <Input
       v-if="key !== 'status'"
       :id="key"
       v-model="form[key]"
       :type="key === 'document' ? 'number' : 'text'"
       :placeholder="label"
       :required="key === 'name' ? true : undefined"
       class="w-full"
      />

            <InputError v-if="key !== 'status'" :message="validationErrors[key]" class="mt-2" />

      <div v-else class="flex items-center gap-3"> 
       <input
        :id="key"
        v-model="form.status"
        type="checkbox"
        class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
       />
       <span class="text-sm text-gray-600 dark:text-gray-300">Activo</span>
      </div> 

            <InputError v-if="key === 'status'" :message="validationErrors.status" class="mt-2" />

     </div> 

     <div class="flex justify-end gap-4 pt-4">
      <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">
       Guardar
      </Button>
      <Button as="a" href="/clients" variant="outline">
       Cancelar
      </Button>
     </div>
    </form>
   </div>    
  </div>
 </AppLayout>
</template>
