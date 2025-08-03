<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <!-- Modern Avatar with enhanced styling -->
    <Avatar class="h-9 w-9 overflow-hidden rounded-xl border-2 border-border/50 bg-gradient-to-br from-primary/10 to-accent/10">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" class="object-cover" />
        <AvatarFallback class="rounded-xl bg-gradient-to-br from-primary/20 to-accent/20 font-semibold text-primary border-0">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <!-- Enhanced Text Layout -->
    <div class="grid flex-1 text-left leading-tight">
        <span class="truncate font-semibold text-foreground text-sm">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground font-medium">{{ user.email }}</span>
    </div>
</template>
