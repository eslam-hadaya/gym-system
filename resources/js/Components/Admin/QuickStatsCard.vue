<script setup>
defineProps({
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
        default: '',
    },
    icon: {
        type: String,
        default: '⚡',
    },
    badge: {
        type: String,
        default: '',
    },
    trend: {
        type: String,
        default: 'neutral', // 'positive', 'negative', 'warning', 'neutral'
    },
});
</script>

<template>
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md space-y-3 relative overflow-hidden group hover:border-amber-500/30 transition duration-200">
        <!-- ترويسة البطاقة والأيقونة -->
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-semibold text-slate-300">{{ title }}</span>
            <div class="w-9 h-9 rounded-xl bg-slate-950/80 border border-slate-800 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
                {{ icon }}
            </div>
        </div>

        <!-- القيمة الرقمية ومؤشر الحركة -->
        <div class="flex items-baseline justify-between gap-2">
            <span class="text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">
                {{ value }}
            </span>

            <span
                v-if="badge"
                :class="[
                    'text-[10px] px-2 py-0.5 rounded-md font-bold font-mono',
                    trend === 'positive' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '',
                    trend === 'negative' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '',
                    trend === 'warning' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '',
                    trend === 'neutral' ? 'bg-slate-800 text-slate-400' : ''
                ]"
            >
                {{ badge }}
            </span>
        </div>

        <!-- نص وصفي سفلي إن وجد -->
        <p v-if="subtitle" class="text-[11px] text-slate-500 truncate pt-1 border-t border-slate-800/60">
            {{ subtitle }}
        </p>
    </div>
</template>
