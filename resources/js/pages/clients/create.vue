<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
//import { Client, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';

//import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
//iconos
// import { Pencil, Trash, CirclePlus } from 'lucide-vue-next';
import { computed, ref  } from 'vue';

// breadcrumbs
type BreadcrumbItem = {title: string, href: string};
const breadcrumbs: BreadcrumbItem[]= [
    {title: 'Clients', href: '/clients'},
    {title: 'Create', href: '#'}
];
const form = ref<Partial<{
  name: string;
  lastname: string;
  document: string;
  status: boolean;
}>>({
    name: '',
    lastname: '',
    document: '',
    status: false,
});

const resetForm = () => {
    form.value ={name: '', lastname: '', document: '', status: false}
}

const submit = () => {
    router.post('/clients', form.value, { onSuccess: resetForm})  
}

</script>

<template>
    <Head title="Create Client"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Create</h1>
            <form @submit.prevent="submit" class="space-y-6 max-w-lg">
                <div v-for="(label, key) in {name: 'Name', lastname: 'Lastname', document: 'Document', status: 'Status'}" 
                    :key="key" 
                    :class="key === 'status' ? 'flex items-center gap-2' : 'space-y-2'" 
                >
                    <Label :for="key">{{ label }}</Label>
                    <!-- Componente personalizado para inputs normales -->
                    <Input 
                        v-if="key !== 'status'"
                        :id="key"
                        v-model="form[key]"
                        type="text"
                        :placeholder="label"
                        :required="key === 'document' ? true : undefined"
                    />

                    <!-- Checkbox nativo solo para 'status' -->
                    <input 
                        v-else
                        :id="key"
                        v-model="form.status"
                        type="checkbox"
                        class="h-5 w-5"
                    />
                </div>
                <div class="flex gap-4">
                    <Button type="submit" class="bg-indigo-500 hover:bg-indigo-700">Save</Button>
                    <Button type="button" @click="$inertia.visit('/clients')" variant="outline">Cancel</Button>
                </div>
            </form>
        </div>
        
    </AppLayout>
</template>