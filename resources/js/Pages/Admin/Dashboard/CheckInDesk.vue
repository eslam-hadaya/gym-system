<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    todayLogs: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_today: 0,
            valid_count: 0,
            rejected_count: 0,
        }),
    },
});

const scanInputRef = ref(null);

const form = useForm({
    member_code: '',
});

const handleScan = () => {
    if (!form.member_code) return;

    form.post('/admin/dashboard/checkin/scan', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('member_code');
            scanInputRef.value?.focus();
        },
        onError: () => {
            scanInputRef.value?.focus();
        },
    });
};
</script>

<template>
    <Head title="مكتب الاستقبال وماسح الـ QR" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- الترويسة -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">مكتب استقبال وفحص بطاقات الدخول</h1>
                    <p class="text-xs text-slate-400 mt-1">مسح بطاقات الـ QR للاعبين والتحقق الفوري من صلاحية الاشتراك وفترة التدريب</p>
                </div>
            </div>

            <!-- بطاقات مؤشرات الحضور لليوم -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">إجمالي الحضور اليوم</span>
                    <p class="text-2xl font-black text-white font-mono">{{ stats.total_today ?? 0 }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">دخول نظامي ومصرح</span>
                    <p class="text-2xl font-black text-emerald-400 font-mono">{{ stats.valid_count ?? 0 }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">محاولات مرفوضة (منتهي / مخالف)</span>
                    <p class="text-2xl font-black text-rose-400 font-mono">{{ stats.rejected_count ?? 0 }}</p>
                </div>
            </div>

            <!-- حقل المسح السريع بالباركود أو الإدخال اليدوي -->
            <div class="p-6 rounded-3xl bg-slate-900/90 border border-amber-500/30 shadow-2xl relative overflow-hidden">
                <div class="max-w-xl mx-auto space-y-4 text-center">
                    <div class="w-12 h-12 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-2xl flex items-center justify-center text-xl mx-auto">
                        📷
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white">مسح كود اللاعب (Barcode / QR Scanner)</h2>
                        <p class="text-xs text-slate-400 mt-0.5">وجّه قارئ الباركود أو اكتب كود العضوية واضغط Enter</p>
                    </div>

                    <form @submit.prevent="handleScan" class="flex gap-2">
                        <input
                            ref="scanInputRef"
                            v-model="form.member_code"
                            type="text"
                            placeholder="مثال: GYM-1001"
                            autofocus
                            class="flex-1 px-4 py-3 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm font-mono text-white placeholder-slate-600 outline-none transition uppercase"
                            :class="{ 'border-rose-500': form.errors.member_code }"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing || !form.member_code"
                            class="px-6 py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
                        >
                            <span v-if="form.processing" class="w-3.5 h-3.5 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                            <span>فحص الباب</span>
                        </button>
                    </form>

                    <p v-if="form.errors.member_code" class="text-xs text-rose-400 font-medium">
                        {{ form.errors.member_code }}
                    </p>
                </div>
            </div>

            <!-- جدول حركات المسح المسجلة اليوم -->
            <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-white">سجل الدخول المسجل اليوم (Check-ins Log)</h3>
                    <span class="text-[10px] font-mono text-slate-500">
                        {{ (todayLogs && todayLogs.length) ? `${todayLogs.length} حركة` : 'لا توجد حركات' }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">الوقت</th>
                                <th class="py-3 px-4">كود العضوية</th>
                                <th class="py-3 px-4">اللاعب</th>
                                <th class="py-3 px-4">الفترة</th>
                                <th class="py-3 px-4">الباقة</th>
                                <th class="py-3 px-4">القرار الأمني</th>
                                <th class="py-3 px-4">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-if="!todayLogs || todayLogs.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                                    لم تسجل أي حركة دخول عبر ماسح الباب اليوم حتى الآن
                                </td>
                            </tr>
                            <tr v-for="log in (todayLogs || [])" :key="log.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 font-mono text-amber-400">{{ log.time }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-white">{{ log.code }}</td>
                                <td class="py-3 px-4 font-bold text-white">{{ log.name }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-[11px] text-slate-300">
                                        {{ log.gender === 'أنثى' ? '🚺 صباحي' : '🚹 مسائي' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ log.plan }}</td>
                                <td class="py-3 px-4">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-md text-[10px] font-bold border',
                                            log.status === 'valid'
                                                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                                : 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                        ]"
                                    >
                                        {{ log.status === 'valid' ? '✓ دخول مصرح' : '✕ غير مصرح' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ log.note || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
