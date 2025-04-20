<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue'; // Importar AppSidebar
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'; // Importar AppSidebarHeader
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3'; // *** Importar usePage ***
import { computed } from 'vue'; // *** Importar computed ***

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// *** Obtener datos del usuario para pasar el nombre de la empresa a AppSidebar ***
const page = usePage();
const user = computed(() => page.props.auth.user);

</script>

<template>
    <AppShell variant="sidebar">
            <AppSidebar :company-name="user?.empresa?.name" />
        <AppContent variant="sidebar" :breadcrumbs="breadcrumbs"> 
                    <slot />
         </AppContent>
     </AppShell>
</template>