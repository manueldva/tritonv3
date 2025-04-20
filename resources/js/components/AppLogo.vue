<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
// Si quieres usar otro icono de lucide-vue-next en lugar de AppLogoIcon, impórtalo aquí
// import { BuildingStorefront } from 'lucide-vue-next'; // Ejemplo

// *** DEFINIR LA PROP companyName ***
const props = defineProps({
    companyName: {
        type: String,
        default: null, // Valor por defecto si no se pasa la prop o es null/undefined
    }
});

</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
     </div>
 <div class="ml-1 grid flex-1 text-left text-sm">
         <span class="mb-0.5 truncate font-semibold leading-none">
            {{ props.companyName || 'App de Gestión' }} 
        </span>
 </div>
</template>