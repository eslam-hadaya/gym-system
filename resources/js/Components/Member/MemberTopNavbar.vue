<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

defineEmits(['toggleSidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'اللاعب' });

const member = computed(() => page.props.member || null);
const subscriptionStatus = computed(() => member.value?.status || 'pending');
const memberCode = computed(() => member.value?.member_code || '—');

const todayDate = new Intl.DateTimeFormat('ar-SY', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
}).format(new Date());

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <header class="h-20 bg-slate-900/70 backdrop-blur-xl border-b border-slate-800/80 sticky top-0 z-30 px-6 sm:px-8 flex items-center justify-between select-none" dir="rtl">
        <!-- الجهة اليمنى: زر القائمة للشاشات الصغيرة والترحيب والتاريخ -->
        <div class="flex items-center gap-4">
            <button
                type="button"
                @click="$emit('toggleSidebar')"
                class="md:hidden p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition cursor-pointer"
                title="القائمة"
            >
                <span class="text-lg">☰</span>
            </button>

            <div class="flex flex-col">
                <span class="text-xs font-bold text-white">أهلاً بك، {{ user.name }} 👋</span>
                <span class="text-[10px] text-slate-400">{{ todayDate }}</span>
            </div>
        </div>

        <!-- الجهة اليسرى: حالة الاشتراك، رقم العضوية، وزر الخروج -->
        <div class="flex items-center gap-3">
            <!-- حالة صلاحية الاشتراك الحالية -->
            <div
                v-if="subscriptionStatus === 'active'"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400 font-bold"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="hidden sm:inline">اشتراكك سارٍ</span>
                <span class="sm:hidden">سارٍ</span>
            </div>

            <div
                v-else
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400 font-bold"
            >
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span class="hidden sm:inline">بانتظار الاشتراك</span>
                <span class="sm:hidden">معلق</span>
            </div>

            <!-- بطاقة كود اللاعب السريع -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs text-amber-400 font-bold">
                <span class="text-slate-500 text-[10px]">ID:</span>
                <span>{{ memberCode }}</span>
            </div>

            <!-- زر تسجيل الخروج السريع -->
            <button
                type="button"
                @click="handleLogout"
                class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                title="تسجيل الخروج"
            >
                <span>🚪</span>
                <span class="hidden sm:inline">خروج</span>
            </button>
        </div>
    </header>
</template>
