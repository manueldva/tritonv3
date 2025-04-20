<script setup lang="ts">
// Importar componentes que AppContent pueda usar (como AppSidebarHeader)
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types'; // Asegúrate de que el tipo BreadcrumbItemType esté disponible

import { usePage } from '@inertiajs/vue3';
import { watch, ref } from 'vue'; // *** Importar watch y ref ***

// Props que AppContent pueda recibir (como breadcrumbs)
interface Props {
     breadcrumbs?: BreadcrumbItemType[];
    // Si AppContent recibe la prop 'variant', definirla aquí
    // variant?: string;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});


// *** Lógica para Mensajes Flash ***
const page = usePage(); // Necesita usePage para observar los props flash
const showSuccess = ref(false);
const showError = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Observar los props flash de Inertia
watch(() => page.props.flash, (flash) => {
    console.log('Watcher: Flash props changed in AppContent:', flash); // Log de depuración
    // Asegurarse de que flash no sea null/undefined antes de acceder a sus propiedades
    const flashData = flash || {};

    // Si hay un mensaje 'success' en los props flash
    if (flashData.success) {
        console.log('Watcher: Success message detected in AppContent:', flashData.success); // Log de depuración
        errorMessage.value = ''; // Ocultar error si aparece éxito
        showError.value = false;
        successMessage.value = flashData.success;
        showSuccess.value = true;
        // Opcional: Ocultar automáticamente después de 5 segundos
        // setTimeout(() => { showSuccess.value = false; successMessage.value = ''; }, 5000);
    } else {
        showSuccess.value = false; // Ocultar si ya no hay mensaje
        successMessage.value = ''; // Limpiar el mensaje
    }

    // Si hay un mensaje 'error' en los props flash
    if (flashData.error) {
        console.log('Watcher: Error message detected in AppContent:', flashData.error); // Log de depuración
         successMessage.value = ''; // Ocultar éxito si aparece error
         showSuccess.value = false;
        errorMessage.value = flashData.error;
        showError.value = true;
        // Opcional: Ocultar automáticamente después de 5 segundos
        // setTimeout(() => { showError.value = false; errorMessage.value = ''; }, 5000);
    } else {
        showError.value = false; // Ocultar si ya no hay mensaje
        errorMessage.value = ''; // Limpiar el mensaje
    }

    // Puedes añadir lógica para otros tipos de mensajes (info, warning, etc.) si los usas
    // if (flashData.info) { ... }

}, { immediate: true }); // El 'immediate: true' hace que el watcher se ejecute al montar el componente si ya hay mensajes

// Métodos para cerrar manualmente los mensajes (si no usas auto-ocultar)
const closeSuccessMessage = () => { showSuccess.value = false; successMessage.value = ''; };
const closeErrorMessage = () => { showError.value = false; errorMessage.value = ''; };

</script>

<template>
    <div class="flex-1 p-6"> 
        <AppSidebarHeader :breadcrumbs="breadcrumbs" /> 

        <div class="mb-6 mt-4"> 
            <div v-if="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Éxito!</strong>
                <span class="block sm:inline">{{ successMessage }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="closeSuccessMessage">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15L6.305 6.849a1.2 1.2 0 0 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.15 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>

            <div v-if="showError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ errorMessage }}</span>
                 <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="closeErrorMessage">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15L6.305 6.849a1.2 1.2 0 0 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.15 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>

            </div>
        <slot />
    </div>
</template>
