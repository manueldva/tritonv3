<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Client, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';

import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

import { Button } from '@/components/ui/button';
//iconos
import { Pencil, Trash, CirclePlus } from 'lucide-vue-next';
import { computed  } from 'vue';


interface ClientPageProps extends SharedData {
    clients: Client[];
}

const {props} = usePage<ClientPageProps>();
const clients = computed(()=>props.clients);

// breadcrumbs
const breadcrumbs: BreadcrumbItem[]= [{title: 'Clients', href: '/clients'}];

//metodo eliminar
const deleteClient = async (id: number) => {
    if(!window.confirm('Estas Seguro?')) return;

    router.delete(`clients/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/clients', { replace: true}); // redirige a la lista actualizada
        },
        onError: (errors) => {
            console.error('Error al eliminar el cliente:',  errors);
        }
    });
};
</script>

<template>
    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
       <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex">
                <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
                    <Link href="/clients/create"><CirclePlus /> Create </Link>
                </Button>
            </div>
        
        <div class="relative min-h[100vh] flex-1 rounded-cl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <Table>
                    <TableCaption>Client List.</TableCaption>   
                    <TableHeader>
                        <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>LastName</TableHead>
                                <TableHead>Document</TableHead>
                                <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="client in clients" :key="client.id">
                            <TableCell class="font-medium">{{ client.name }}</TableCell>
                            <TableCell class="font-medium">{{ client.lastname }}</TableCell>
                            <TableCell>{{ client.document }}</TableCell>
                            <TableCell>{{ client.status }}</TableCell>
                            <TableCell class="flex justify-center gap-2">
                                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                                    <Link :href="`/clients/${client.id}/edit`"> 
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 text-white hover:bg-rose-700" @click="deleteClient(client.id)">
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
