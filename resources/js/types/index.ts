import type { LucideIcon } from 'lucide-vue-next';

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

export type FlashPayload = string | { key: string; params?: Record<string, unknown> };

export interface EmailLogEntry {
    id: number | string;
    subject: string;
    body: string;
    sent_at: string | null;
    recipient_count: number;
    recipients: string[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth & { admin: Admin | null };
    flash: { status: FlashPayload | null; error: FlashPayload | null };
    impersonating: boolean;
    serviceCenters: Record<string, boolean>;
    recentEmailLogs: EmailLogEntry[];
    emailLogCount: number;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Admin {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'staff';
    is_active: boolean;
}

export type BreadcrumbItemType = BreadcrumbItem;
