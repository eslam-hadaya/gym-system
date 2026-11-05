<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const historyLogs = ref([
    { id: 1, date: '2026-11-04', time: '17:35', status: 'valid', note: 'دخول مسائي اعتيادي' },
    { id: 2, date: '2026-11-03', time: '18:10', status: 'valid', note: 'دخول مسائي اعتيادي' },
    { id: 3, date: '2026-11-01', time: '16:50', status: 'valid', note: 'دخول مسائي اعتيادي' },
    { id: 4, date: '2026-10-30', time: '19:00', status: 'valid', note: 'دخول مسائي اعتيادي' },
    { id: 5, date: '2026-10-28', time: '17:15', status: 'valid', note: 'دخول مسائي اعتيادي' },
]);
</script>

<template>
    <Head title="سجل حركات الحضور" />

    <MemberLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-black text-white">سجل حركات الحضور والتمارين</h1>
                <p class="text-xs text-slate-400 mt-1">تتبع التزامك الأسبوعي وأوقات دخولك المسجلة عبر الباب</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">إجمالي الحضور هذا الشهر</span>
                    <p class="text-2xl font-black text-white">14 <span class="text-xs font-normal text-slate-400">تمرين</span></p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <span class="text-xs text-slate-400">أعلى وقت حضور متكرر</span>
                    <p class="text-2xl font-black text-amber-400">05:30 م</p>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h2 class="text-xs font-bold text-white">سجل المسح الرقمي (Check-ins Log)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">تاريخ التمرين</th>
                                <th class="py-3 px-4">وقت الدخول عبر الـ QR</th>
                                <th class="py-3 px-4">حالة الباب</th>
                                <th class="py-3 px-4">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-for="log in historyLogs" :key="log.id" class="hover:bg-slate-800/30">
                                <td class="py-3.5 px-4 font-mono font-bold text-white">{{ log.date }}</td>
                                <td class="py-3.5 px-4 font-mono text-amber-400">{{ log.time }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">
                                        ✓ دخول مصادق
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400">{{ log.note }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
