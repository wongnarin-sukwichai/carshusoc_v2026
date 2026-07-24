<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';

const sidebarNavItems = [
    { title: 'Profile', routeName: 'profile.edit' },
    { title: 'Password', routeName: 'password.edit' },
    { title: 'Appearance', routeName: 'appearance' },
];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 p-6 text-white">
            <Settings class="absolute right-0 bottom-0 h-48 w-48 translate-x-12 translate-y-6 opacity-10" />
            <div class="relative z-10 max-w-2xl">
                <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase"> Settings </span>
                <h2 class="mt-2 text-xl font-extrabold">Account Settings</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-300">Manage your profile, password, and appearance preferences.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <nav class="space-y-1.5">
                        <Link
                            v-for="item in sidebarNavItems"
                            :key="item.routeName"
                            :href="route(item.routeName)"
                            class="flex items-center rounded-xl border-l-4 px-3 py-3 text-left text-xs font-bold transition-all"
                            :class="
                                route().current(item.routeName)
                                    ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                    : 'border-transparent text-slate-600 hover:bg-slate-50'
                            "
                        >
                            {{ item.title }}
                        </Link>
                    </nav>
                </div>
            </aside>

            <div class="lg:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <section class="space-y-12">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
