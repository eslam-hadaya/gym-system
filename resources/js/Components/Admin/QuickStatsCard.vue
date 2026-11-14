<script setup>
import { computed } from "vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    subtitle: {
        type: String,
        default: "",
    },
    icon: {
        type: String,
        default: "⚡",
    },
    badge: {
        type: String,
        default: "",
    },
    trend: {
        type: String,
        default: "neutral",
        validator: (val) =>
            ["positive", "negative", "warning", "neutral"].includes(val),
    },
});

const formattedValue = computed(() => {
    if (typeof props.value === "number") {
        return props.value.toLocaleString();
    }
    return props.value;
});

// تعديل بسيط في الألوان لتتوافق مع توكنز نظامنا
const badgeStyles = {
    positive:
        "bg-success-500/10 text-success-600 dark:text-success-400 border-success-500/20",
    negative: "bg-danger-500/10 text-danger-500 border-danger-500/20",
    warning:
        "bg-warning-500/10 text-warning-600 dark:text-warning-400 border-warning-500/20",
    neutral: "bg-glass-bg text-ink-muted border-line-strong",
};
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated p-5 space-y-3 transition-colors duration-200 hover:border-brand-500/30"
    >
        <!-- ترويسة البطاقة والأيقونة -->
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-ink-secondary">
                {{ title }}
            </span>

            <!-- حاوية الأيقونة -->
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-line-strong bg-glass-bg text-base"
            >
                <!--
                  إذا كانت الأيقونة نصاً (إيموجي)، نعرضها كما هي.
                  (ملاحظة: يمكنك لاحقاً تمرير مكونات Lucide كـ props إذا أردت)
                -->
                <span v-if="typeof icon === 'string'">{{ icon }}</span>
                <component v-else :is="icon" class="h-4 w-4" />
            </div>
        </div>

        <!-- القيمة الرقمية ومؤشر الحركة -->
        <div class="flex items-baseline justify-between gap-2">
            <span
                class="font-mono text-2xl font-black text-ink-primary tracking-tight sm:text-3xl"
            >
                {{ formattedValue }}
            </span>

            <span
                v-if="badge"
                class="rounded-md border px-2 py-0.5 font-mono text-[10px] font-bold"
                :class="badgeStyles[trend]"
            >
                {{ badge }}
            </span>
        </div>

        <!-- نص وصفي سفلي -->
        <p
            v-if="subtitle"
            class="truncate border-t border-line-soft pt-1 text-[11px] text-ink-muted"
        >
            {{ subtitle }}
        </p>
    </div>
</template>
