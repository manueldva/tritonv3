import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

// resources/js/types/index.d.ts or similar
export interface Empresa {
    id: number;
    name: string;
    ruc: string | null;
    address: string | null;
    phone: string | null;
    email: string | null;
    status: boolean;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_admin: boolean;
    is_active: boolean;
    empresa_id: number;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Client {
    id: number;
    name: string;
    lastname: string;
    document: string;
    status: boolean;
    empresa_id: number;
}