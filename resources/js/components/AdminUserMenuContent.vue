<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { Admin } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

interface Props {
    admin: Admin;
    // See UserMenuContent.vue — the dropdown normally repeats the admin's
    // name/email as its own header, redundant once the trigger already
    // shows the full identity.
    showIdentity?: boolean;
}

withDefaults(defineProps<Props>(), { showIdentity: true });
</script>

<template>
    <template v-if="showIdentity">
        <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                <UserInfo :user="admin" :show-email="true" />
            </div>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
    </template>
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" method="post" :href="route('admin.logout')" as="button">
                <LogOut class="mr-2 h-4 w-4" />
                Log out
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
