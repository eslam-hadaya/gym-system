<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

defineProps({
    member: {
        type: Object,
        required: true,
    },
    checkIns: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({ total_checkins: 0, this_month: 0 }),
    },
});
</script>

<template>
    <Head title="سجل حركات الحضور" />

    <MemberLayout>
        <div class="space-y-6" dir="rtl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white">سجل الحركات والتمارين</h1>
                    <p class="text-xs text-slate-400 mt-1">تتبع التزامك الرياضي وحركات دخول الصالة المسجلة بالـ QR</p>
                </div>
            </div>

            <!-- إحصائيات التزام اللاعب -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">إجمالي مرات التمرين المسجلة</span>
                    <p class="text-2xl font-black text-amber-400 font-mono">{{ stats.total_checkins }} <span class="text-xs text-slate-400 font-sans">تمرين</span></p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">حضور هذا الشهر</span>
                    <p class="text-2xl font-black text-emerald-400 font-mono">{{ stats.this_month }} <span class="text-xs text-slate-400 font-sans">أيام</span></p>
                </div>
            </div>

            <!-- جدول الحركات -->
            <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3.5 px-4">التاريخ</th>
                                <th class="py-3.5 px-4">توقيت الدخول</th>
                                <th class="py-3.5 px-4">الحالة</th>
                                <th class="py-3.5 px-4">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-if="checkIns.data.length === 0">
                                <td colspan="4" class="py-8 text-center text-slate-500 text-xs">
                                    لا توجد حركات حضور مسجلة حتى الآن.
                                </td>
                            </tr>
                            <tr v-for="entry in checkIns.data" :key="entry.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-white">{{ entry.date }}</td>
                                <td class="py-3.5 px-4 font-mono text-amber-400">{{ entry.time }}</td>
                                <td class="py-3.5 px-4">
                                    <span
                                        v-if="entry.status === 'valid'"
                                        class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold"
                                    >
                                        دخول ناجح
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold"
                                    >
                                        مرفوض / منتهي
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400">{{ entry.note }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- الترقيم -->
                <div v-if="checkIns.links && checkIns.links.length > 3" class="p-4 border-t border-slate-800 flex justify-center gap-1">
                    <Link
                        v-for="(link, index) in checkIns.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 text-xs rounded-lg border transition"
                        :class="link.active ? 'bg-amber-500 text-slate-950 border-amber-500 font-bold' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                        :disabled="!link.url"
                    />
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
