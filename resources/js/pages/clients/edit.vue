<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, onMounted } from 'vue';
import type { Client } from '@/types';

// Breadcrumbs
type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: '/clients' },
  { title: 'Editar', href: '#' }
];

// Cliente desde props
const { props } = usePage();
const client = props.client as Client;

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
    document: client.document ?? '',
    status: !!client.status
  };
});

const resetForm = () => {
  form.value = {
    name: '',
    lastname: '',
    document: '',
    status: false
  };
};

const submit = () => {
  router.put(`/clients/${client.id}`, form.value, {
    onSuccess: resetForm
  });
};
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
            :class="key === 'status' ? 'flex items-center gap-3' : 'space-y-2'"
          >
            <Label :for="key" class="text-gray-700 dark:text-gray-300">{{ label }}</Label>

            <Input
              v-if="key !== 'status'"
              :id="key"
              v-model="form[key]"
              type="text"
              :placeholder="label"
              :required="key === 'document' ? true : undefined"
              class="w-full"
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
            <Button as="a" href="/clients" variant="outline">
              Cancelar
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
