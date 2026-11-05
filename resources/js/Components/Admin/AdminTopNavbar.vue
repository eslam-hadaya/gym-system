<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineEmits(['toggleSidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'المسؤول' });

// صياغة التاريخ بالعربية
const todayDate = new Intl.DateTimeFormat('ar-SY', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
}).format(new Date());
</script>

<template>
    <header class="h-20 bg-slate-900/70 backdrop-blur-xl border-b border-slate-800/80 sticky top-0 z-30 px-6 sm:px-8 flex items-center justify-between">
        <!-- الجهة اليسرى: زر القائمة للشاشات الصغيرة وتاريخ اليوم -->
        <div class="flex items-center gap-4">
            <!-- زر إظهار السايدبار للهواتف -->
            <button
                type="button"
                @click="$emit('toggleSidebar')"
                class="md:hidden p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                title="القائمة"
            >
                <span class="text-lg">☰</span>
            </button>

            <!-- تاريخ اليوم والموقع -->
            <div class="hidden sm:flex flex-col">
                <span class="text-xs font-bold text-white">{{ todayDate }}</span>
                <span class="text-[10px] text-slate-500 font-mono">الوردية الحالية: استقبال وتسجيل</span>
            </div>
        </div>

        <!-- الجهة اليمنى: المؤشرات التشغيلية للمنظومة -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- مؤشر جاهزية قارئ الباب -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-slate-400">ماسح الـ QR:</span>
                <span class="text-emerald-400 font-bold font-mono">متصل ومتاح</span>
            </div>

            <!-- مؤشر السيرفر وقاعدة البيانات -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs font-medium text-amber-400">
                <span>⚡</span>
                <span class="hidden sm:inline">السيرفر:</span>
                <span class="font-mono text-[11px]">Neon DB</span>
            </div>

            <!-- اسم المدير في الوردية -->
            <div class="flex items-center gap-2 pr-3 border-r border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-xs">
                    🛡️
                </div>
                <div class="hidden md:block text-right">
                    <p class="text-xs font-bold text-white leading-tight">{{ user.name }}</p>
                    <p class="text-[10px] text-slate-400 leading-tight">صلاحية إدارية كاملة</p>
                </div>
            </div>
        </div>
    </header>
</template>
