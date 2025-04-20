<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
// *** Importar Head, Link, router, y usePage ***
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Importar usePage
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// *** Importar el componente InputError ***
import InputError from '@/components/InputError.vue'; // Asegúrate de que esta ruta sea correcta
import { ref, computed } from 'vue'; // Necesitas ref y computed

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Usuarios', href: '/manageusers' },
  { title: 'Create', href: '#' }
];

const form = ref<Partial<{
  name: string;
  email: string;
  is_active: boolean;
}>>({
  name: '',
  email: '',
  is_active: true,
});

const resetForm = () => {
  form.value = { name: '', email: '', is_active: true };
};

const submit = () => {
  router.post('/manageusers', form.value, { onSuccess: resetForm });
};

// *** Acceder a los errores de validación desde los props de la página ***
const page = usePage();

// Crear una propiedad computada para acceder a los errores de forma segura
// Esto asegura que si page.props.errors es null/undefined, no cause un error
const validationErrors = computed(() => page.props.errors || {});


</script>

<template>

  <Head title="Crear Usuario" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 justify-center p-6">
      <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-md p-8 space-y-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Usuario</h1>

        <form @submit.prevent="submit" class="space-y-6">
          <div v-for="(label, key) in { name: 'Nombre', email: 'Email', is_active: 'Estado' }"
            :key="key" 
            class='space-y-2'
          >
            <Label :for="key" class="text-gray-700 dark:text-gray-300">{{ label }}</Label>

            <Input v-if="key !== 'is_active'" 
            :id="key" v-model="form[key]" 
            :type="key === 'email' ? 'email' : 'text'"
            :placeholder="label" 
            :required=true class="w-full" />

            <InputError v-if="key !== 'is_active'" 
            :message="validationErrors[key]" class="mt-2" />

            <div v-else class="flex items-center gap-3">
              <input 
              :id="key" 
              v-model="form.is_active" 
              type="checkbox"
                class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" />
              <span class="text-sm text-gray-600 dark:text-gray-300">Activo</span>
            </div>
            <InputError v-if="key === 'is_active'" 
            :message="validationErrors.status" class="mt-2" />
          </div>

          <div class="flex justify-end gap-4 pt-4">
            <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">
              Guardar
            </Button>
            <Button as="a" href="/manageusers" variant="outline">
              Cancelar
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
