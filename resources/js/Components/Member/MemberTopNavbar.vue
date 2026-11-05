<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineEmits(['toggleSidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'اللاعب' });

// قراءة حالة الاشتراك مباشرة دون تحويل نوع
const subscriptionStatus = computed(() => page.props.member?.status || 'active');
const memberCode = computed(() => page.props.member?.member_code || '1042');

const todayDate = new Intl.DateTimeFormat('ar-SY', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
}).format(new Date());
</script>

<template>
    <header class="h-20 bg-slate-900/70 backdrop-blur-xl border-b border-slate-800/80 sticky top-0 z-30 px-6 sm:px-8 flex items-center justify-between">
        <!-- الجهة اليسرى: زر القائمة والتاريخ -->
        <div class="flex items-center gap-4">
            <button
                type="button"
                @click="$emit('toggleSidebar')"
                class="md:hidden p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                title="القائمة"
            >
                <span class="text-lg">☰</span>
            </button>

            <div class="flex flex-col">
                <span class="text-xs font-bold text-white">أهلاً بك، {{ user.name }} 👋</span>
                <span class="text-[10px] text-slate-500">{{ todayDate }}</span>
            </div>
        </div>

        <!-- الجهة اليمنى: حالة الاشتراك ورقم العضوية -->
        <div class="flex items-center gap-3">
            <!-- حالة صلاحية الاشتراك الحالية -->
            <div
                v-if="subscriptionStatus === 'active'"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400 font-bold"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>اشتراكك ساري</span>
            </div>

            <div
                v-else
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-400 font-bold"
            >
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span>الاشتراك بانتظار التجديد</span>
            </div>

            <!-- بطاقة كود اللاعب السريع -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs text-amber-400 font-bold">
                <span>GYM-ID:</span>
                <span>{{ memberCode }}</span>
            </div>
        </div>
    </header>
</template>
