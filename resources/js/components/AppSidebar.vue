<script setup lang="ts">
// Importar los componentes internos de AppSidebar
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
// Importar los componentes UI de la sidebar (si AppSidebar los usa internamente)
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

import { type NavItem } from '@/types'; // Asegúrate de que NavItem coincida con la estructura { title, href, icon }
import { Link, usePage } from '@inertiajs/vue3';
// *** Importar TODOS los iconos que puedan usarse en el menú, incluyendo el fallback Dot ***
import { BookOpen, Folder, LayoutGrid, Castle, PersonStanding, Dot } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue'; // Asegúrate de que la ruta a AppLogo sea correcta desde AppSidebar.vue
import { computed } from 'vue';

// *** Mapeo de nombres de icono (string de la DB) a componentes de icono reales ***
const iconMap = {
    BookOpen: BookOpen,
    Folder: Folder,
    LayoutGrid: LayoutGrid,
    Castle: Castle,
    PersonStanding: PersonStanding,
    // Añade aquí cualquier otro icono que uses en tu tabla 'menu_items'
    Dot: Dot, // Incluir Dot en el mapa o manejar fallback explícitamente
};

// Acceder a los props de Inertia (para usuario y ítems de menú)
const page = usePage();
const user = computed(() => page.props.auth.user); // Acceder al usuario de forma reactiva

// *** ACCEDER A LOS ÍTEMS DEL MENÚ COMPARTIDOS GLOBALMENTE, MAPEAR ICONOS Y GENERAR URLs ***
const mainNavItems = computed<NavItem[]>(() => {
    // Esperamos que page.props.menuItems sea un array de objetos { title: string, href: string (nombre de ruta), icon: string (nombre de icono) }
    const itemsFromBackend = page.props.menuItems as { title: string; href: string; icon: string }[] || [];

    // Mapear los ítems para:
    // 1. Reemplazar el string del nombre del icono por el componente de icono real usando iconMap, con Dot como fallback.
    // 2. Generar la URL real usando la función route() con el nombre de ruta de la DB.
    return itemsFromBackend.map(item => {
        const iconComponent = iconMap[item.icon] || Dot; // Usar Dot como componente por defecto/fallback

        let generatedHref = '#'; // Valor por defecto si la ruta no existe o hay un error
        try {
             generatedHref = route(item.href); // Asume que item.href es el nombre de la ruta (ej: 'clients.index')
        } catch (e) {
            console.error(`Error generating route for item: ${item.href}`, e);
            // Opcional: Podrías devolver null o un ítem deshabilitado si la ruta falla
        }

        return {
            title: item.title,
            href: generatedHref, // Usar la URL generada
            icon: iconComponent, // Ahora siempre será un componente (el mapeado o Dot)
        };
    });
});

// Tu array de footerNavItems (este puede seguir siendo estático o venir también de la DB)
const footerNavItems: NavItem[] = [
    /*
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
    */
];

// Si AppSidebar recibe props (como 'collapsible', 'variant'), definirlas aquí
// const props = defineProps({...});


</script>

<template>
    <Sidebar collapsible="icon" variant="inset"> 
        <SidebarHeader>
             <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                           <AppLogo :company-name="user?.empresa?.name" /> 
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser /> 
        </SidebarFooter>
    </Sidebar>
</template>
