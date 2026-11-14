<script setup>
import { Sun, Moon } from "@lucide/vue";
import { useTheme } from "@/composables/useTheme";

const { theme, toggle } = useTheme();
</script>

<template>
    <button
        type="button"
        @click="toggle"
        :aria-label="
            theme === 'dark' ? 'التبديل للوضع النهاري' : 'التبديل للوضع الليلي'
        "
        :aria-pressed="theme === 'dark'"
        class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-line-strong bg-glass-bg backdrop-blur-glass text-ink-secondary transition-colors duration-200 hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
    >
        <Sun v-if="theme === 'dark'" class="h-4 w-4" :stroke-width="2" />
        <Moon v-else class="h-4 w-4" :stroke-width="2" />
    </button>
</template>
