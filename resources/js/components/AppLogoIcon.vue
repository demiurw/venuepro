<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" :class="className" v-bind="$attrs">
        <!-- Building/Venue icon with modern design -->
        <path
            fill="currentColor"
            d="M28 28V12l-4-4V4a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v4l-4 4v16h24ZM10 6h12v2H10V6Zm0 4h12v2H10v-2Zm12 4H10l2-2h8l2 2ZM6 14h20v12H6V14Zm4 2v2h2v-2h-2Zm6 0v2h2v-2h-2Zm6 0v2h2v-2h-2ZM10 20v2h2v-2h-2Zm6 0v2h2v-2h-2Zm6 0v2h2v-2h-2Z"
        />
        <!-- Calendar/Schedule overlay -->
        <rect
            x="20"
            y="20"
            width="10"
            height="10"
            rx="1"
            fill="currentColor"
            opacity="0.8"
        />
        <path
            fill="white"
            d="M22 22h2v1h-2v-1Zm3 0h2v1h-2v-1ZM22 24h2v1h-2v-1Zm3 0h2v1h-2v-1ZM22 26h2v1h-2v-1Zm3 0h2v1h-2v-1Z"
        />
    </svg>
</template>
