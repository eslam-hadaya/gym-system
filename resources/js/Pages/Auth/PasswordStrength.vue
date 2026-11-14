<script setup>
import { computed } from "vue";

const props = defineProps({
    password: { type: String, default: "" },
});

const strength = computed(() => {
    const p = props.password;
    if (!p) return { score: 0, label: "", color: "" };

    let score = 0;
    if (p.length >= 8) score++;
    if (p.length >= 12) score++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;

    if (score <= 2)
        return {
            score,
            label: "ضعيفة",
            color: "bg-danger-500",
            text: "text-danger-500",
        };
    if (score === 3)
        return {
            score,
            label: "متوسطة",
            color: "bg-yellow-500",
            text: "text-yellow-600 dark:text-yellow-400",
        };
    if (score === 4)
        return {
            score,
            label: "قوية",
            color: "bg-brand-500",
            text: "text-brand-600 dark:text-brand-400",
        };
    return {
        score,
        label: "قوية جداً",
        color: "bg-emerald-500",
        text: "text-emerald-600 dark:text-emerald-400",
    };
});
</script>

<template>
    <div v-if="password" class="space-y-1.5" aria-live="polite">
        <div class="flex gap-1">
            <span
                v-for="i in 4"
                :key="i"
                class="h-1 flex-1 rounded-full transition-colors duration-200"
                :class="
                    i <= Math.min(strength.score, 4)
                        ? strength.color
                        : 'bg-line-soft'
                "
            ></span>
        </div>
        <p class="text-[10px] font-medium" :class="strength.text">
            قوة كلمة المرور: {{ strength.label }}
        </p>
    </div>
</template>
