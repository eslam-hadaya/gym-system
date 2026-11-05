<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const activeTab = ref('all'); // all, cash, shamcash

const transactions = ref([
    { id: 'TXN-901', member: 'أحمد الإبراهيم', amount: 400000, method: 'shamcash', ref: 'SHAM-772190', date: '2026-11-05 11:20', status: 'verified', note: 'اشتراك 3 أشهر' },
    { id: 'TXN-902', member: 'سامر قواس', amount: 150000, method: 'cash', ref: 'مكتب الكاشير', date: '2026-11-05 10:15', status: 'verified', note: 'اشتراك شهر' },
    { id: 'TXN-903', member: 'باسل كنعان', amount: 400000, method: 'shamcash', ref: 'SHAM-984210', date: '2026-11-04 18:40', status: 'pending', note: 'بانتظار مطابقة إشعار الحساب' },
    { id: 'TXN-904', member: 'محمد الصباغ', amount: 1400000, method: 'cash', ref: 'مكتب الكاشير', date: '2026-11-04 16:00', status: 'verified', note: 'اشتراك سنوي VIP' },
]);

const filteredTransactions = computed(() => {
    if (activeTab.value === 'all') return transactions.value;
    return transactions.value.filter(t => t.method === activeTab.value);
});

const totalIncome = computed(() => transactions.value.filter(t => t.status === 'verified').reduce((sum, t) => sum + t.amount, 0));
const shamCashIncome = computed(() => transactions.value.filter(t => t.status === 'verified' && t.method === 'shamcash').reduce((sum, t) => sum + t.amount, 0));
const cashIncome = computed(() => transactions.value.filter(t => t.status === 'verified' && t.method === 'cash').reduce((sum, t) => sum + t.amount, 0));

const verifyTxn = (txn) => {
    txn.status = 'verified';
};
</script>

<template>
    <Head title="الصندوق وشام كاش - الإدارة" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-white">إدارة الصندوق والمدفوعات</h1>
                    <p class="text-xs text-slate-400 mt-1">مطابقة حوالات شام كاش وحركات الصندوق النقدي في الصالة</p>
                </div>
            </div>

            <!-- بطاقات الإيرادات والمطابقة -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-2">
                    <span class="text-xs text-slate-400">إجمالي المقبوضات المعتمدة</span>
                    <p class="text-2xl font-black text-white">{{ totalIncome.toLocaleString() }} <span class="text-xs text-amber-400">ل.س</span></p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-2">
                    <span class="text-xs text-slate-400">مدفوعات شام كاش (Sham Cash)</span>
                    <p class="text-2xl font-black text-emerald-400">{{ shamCashIncome.toLocaleString() }} <span class="text-xs text-slate-400">ل.س</span></p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-2">
                    <span class="text-xs text-slate-400">الصندوق النقدي (مكتب الاستقبال)</span>
                    <p class="text-2xl font-black text-amber-400">{{ cashIncome.toLocaleString() }} <span class="text-xs text-slate-400">ل.س</span></p>
                </div>
            </div>

            <!-- الفلترة والجدول -->
            <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="['px-3 py-1.5 rounded-xl text-xs font-bold transition', activeTab === 'all' ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300']"
                        >
                            كافة العمليات
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'shamcash'"
                            :class="['px-3 py-1.5 rounded-xl text-xs font-bold transition', activeTab === 'shamcash' ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300']"
                        >
                            حوالات شام كاش
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'cash'"
                            :class="['px-3 py-1.5 rounded-xl text-xs font-bold transition', activeTab === 'cash' ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300']"
                        >
                            كاش يدوي
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">رقم الحركة</th>
                                <th class="py-3 px-4">اللاعب</th>
                                <th class="py-3 px-4">المبلغ</th>
                                <th class="py-3 px-4">طريقة الدفع</th>
                                <th class="py-3 px-4">المرجع / إشعار العملية</th>
                                <th class="py-3 px-4">التاريخ</th>
                                <th class="py-3 px-4">الحالة</th>
                                <th class="py-3 px-4 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-for="t in filteredTransactions" :key="t.id" class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-mono text-slate-500">{{ t.id }}</td>
                                <td class="py-3 px-4 font-bold text-white">{{ t.member }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-white">{{ t.amount.toLocaleString() }} ل.س</td>
                                <td class="py-3 px-4">
                                    <span v-if="t.method === 'shamcash'" class="text-emerald-400 font-bold">⚡ شام كاش</span>
                                    <span v-else class="text-amber-400 font-bold">💵 صندوق كاش</span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-400">{{ t.ref }}</td>
                                <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">{{ t.date }}</td>
                                <td class="py-3 px-4">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-md text-[10px] font-bold',
                                            t.status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                        ]"
                                    >
                                        {{ t.status === 'verified' ? 'معتمد' : 'معلق للمطابقة' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button
                                        v-if="t.status === 'pending'"
                                        type="button"
                                        @click="verifyTxn(t)"
                                        class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-[10px] transition"
                                    >
                                        تأكيد المطابقة
                                    </button>
                                    <span v-else class="text-slate-500 text-[11px]">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
