<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { ChevronDown } from 'lucide-vue-next';

const props = defineProps<{
    user: User;
}>();

const { getInitials } = useInitials();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-slate-100 text-black">
            <Avatar class="w-8 h-8 overflow-hidden rounded-lg">
                <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                <AvatarFallback class="text-white bg-indigo-500 rounded-lg">{{ getInitials(user.name) }}</AvatarFallback>
            </Avatar>
            <div class="hidden max-w-[10rem] text-left sm:block">
                <p class="text-xs font-bold truncate">{{ user.name }}</p>
                <p class="truncate text-[10px] text-slate-400">{{ user.email }}</p>
            </div>
            <ChevronDown class="h-3.5 w-3.5 shrink-0 text-slate-400" />
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56 rounded-lg" side="bottom" align="end">
            <UserMenuContent :user="user" :show-identity="false" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
