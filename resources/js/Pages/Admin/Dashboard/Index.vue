<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    stats: {
        type: Array,
        required: true,
    },
    recentCheckIns: {
        type: Array,
        required: true,
    },
    pendingSubscriptions: {
        type: Array,
        required: true,
    },
    expiringSoonSubscriptions: {
        type: Array,
        default: () => [],
    },
});

const approve = (id) => {
    router.post(
        `/admin/dashboard/subscription/${id}/approve`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const reject = (id) => {
    router.post(
        `/admin/dashboard/subscription/${id}/reject`,
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="لوحة التحكم الرئيسية - الإدارة" />

    <AdminLayout>
        <div class="space-y-8">
            <!-- ترويسة الصفحة والأزرار السريعة -->
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
            >
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">
                        نظرة عامة على الصالة
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">
                        متابعة التدفق الحي لدخول اللاعبين والعمليات المالية
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/dashboard/checkin"
                        class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/20"
                    >
                        <span>📷</span>
                        <span>فتح مكتب ماسح الـ QR</span>
                    </Link>

                    <Link
                        href="/admin/dashboard/members"
                        class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 rounded-xl text-xs font-semibold transition"
                    >
                        + قائمة المشتركين
                    </Link>
                </div>
            </div>

            <!-- بطاقات الإحصائيات الأربع -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="(stat, idx) in stats"
                    :key="idx"
                    class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md space-y-3 relative overflow-hidden group hover:border-slate-700 transition"
                >
                    <div
                        class="flex items-center justify-between text-slate-400"
                    >
                        <span class="text-xs font-medium">{{
                            stat.label
                        }}</span>
                        <span
                            class="text-lg p-2 rounded-xl bg-slate-950/60 border border-slate-800"
                        >
                            {{ stat.icon }}
                        </span>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-2xl font-black text-white tracking-tight font-mono"
                        >
                            {{ stat.value }}
                        </span>
                        <span
                            :class="['text-[11px] font-semibold', stat.color]"
                        >
                            {{ stat.change }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- جدول تنبيهات انتهاء الاشتراكات (آخر 5 أيام) -->
            <div
                v-if="expiringSoonSubscriptions.length > 0"
                class="bg-slate-900/60 border border-amber-500/30 rounded-2xl p-6 space-y-4"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-800/80 pb-4"
                >
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"
                        ></span>
                        <h2 class="text-sm font-bold text-white">
                            تنبيهات انتهاء الاشتراكات (خلال 5 أيام)
                        </h2>
                    </div>
                    <span
                        class="text-[11px] px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-400 font-mono font-bold border border-amber-500/20"
                    >
                        {{ expiringSoonSubscriptions.length }} لاعبين
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead
                            class="text-slate-400 font-semibold border-b border-slate-800/60"
                        >
                            <tr>
                                <th class="py-3 px-2">اللاعب</th>
                                <th class="py-3 px-2">كود العضوية</th>
                                <th class="py-3 px-2">نوع الباقة</th>
                                <th class="py-3 px-2">تاريخ الانتهاء</th>
                                <th class="py-3 px-2 text-center">
                                    الأيام المتبقية
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-800/40 text-slate-300"
                        >
                            <tr
                                v-for="sub in expiringSoonSubscriptions"
                                :key="sub.id"
                                class="hover:bg-slate-800/30 transition"
                            >
                                <td class="py-3.5 px-2 font-bold text-white">
                                    {{ sub.member_name }}
                                </td>
                                <td
                                    class="py-3.5 px-2 font-mono text-emerald-400"
                                >
                                    {{ sub.member_code }}
                                </td>
                                <td class="py-3.5 px-2 text-slate-400">
                                    {{ sub.plan_name }}
                                </td>
                                <td
                                    class="py-3.5 px-2 font-mono text-slate-400"
                                >
                                    {{ sub.end_date }}
                                </td>
                                <td class="py-3.5 px-2 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-bold inline-block"
                                        :class="
                                            sub.remaining_days === 0
                                                ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                                                : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                        "
                                    >
                                        {{
                                            sub.remaining_days === 0
                                                ? "ينتهي اليوم"
                                                : sub.remaining_days + " أيام"
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- شبكة السجل الحي والطلبات المعلقة -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- سجل الباب الحي -->
                <div
                    class="lg:col-span-2 bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-800/80 pb-4"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"
                            ></span>
                            <h2 class="text-sm font-bold text-white">
                                آخر حركات الدخول (سجل الباب الحي)
                            </h2>
                        </div>
                        <span class="text-[11px] text-slate-500 font-mono">
                            سجل الحضور الفعلي
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead
                                class="text-slate-400 font-semibold border-b border-slate-800/60"
                            >
                                <tr>
                                    <th class="py-3 px-2">اللاعب</th>
                                    <th class="py-3 px-2">كود العضوية</th>
                                    <th class="py-3 px-2">نوع الباقة</th>
                                    <th class="py-3 px-2">الوقت</th>
                                    <th class="py-3 px-2">حالة الباب</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-800/40 text-slate-300"
                            >
                                <tr v-if="recentCheckIns.length === 0">
                                    <td
                                        colspan="5"
                                        class="py-8 text-center text-slate-500"
                                    >
                                        لا توجد حركات دخول مسجلة حتى اللحظة
                                    </td>
                                </tr>
                                <tr
                                    v-for="entry in recentCheckIns"
                                    :key="entry.id"
                                    class="hover:bg-slate-800/30 transition"
                                >
                                    <td
                                        class="py-3.5 px-2 font-bold text-white flex items-center gap-2"
                                    >
                                        <span
                                            class="w-6 h-6 rounded-lg bg-slate-800 text-[11px] flex items-center justify-center"
                                        >
                                            {{
                                                entry.gender === "ذكر"
                                                    ? "🚹"
                                                    : "🚺"
                                            }}
                                        </span>
                                        <span>{{ entry.name }}</span>
                                    </td>
                                    <td
                                        class="py-3.5 px-2 font-mono text-slate-400"
                                    >
                                        {{ entry.code }}
                                    </td>
                                    <td class="py-3.5 px-2 text-slate-400">
                                        {{ entry.plan }}
                                    </td>
                                    <td class="py-3.5 px-2 text-slate-500">
                                        {{ entry.time }}
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <span
                                            v-if="entry.status === 'valid'"
                                            class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold"
                                        >
                                            ✓ مسموح بالدخول
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 font-bold"
                                        >
                                            ✕ غير مسموح
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- قائمة الاشتراكات بانتظار التأكيد -->
                <div
                    class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-800/80 pb-4"
                    >
                        <h2 class="text-sm font-bold text-white">
                            بانتظار الاعتماد (شام كاش / كاش)
                        </h2>
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 font-bold"
                        >
                            {{ pendingSubscriptions.length }} معلقة
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-if="pendingSubscriptions.length === 0"
                            class="py-8 text-center text-xs text-slate-500"
                        >
                            لا توجد طلبات اشتراك معلقة بانتظار الاعتماد
                        </div>

                        <div
                            v-for="sub in pendingSubscriptions"
                            :key="sub.id"
                            class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2.5 text-xs"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white">
                                    {{ sub.name }}
                                </span>
                                <span
                                    class="text-amber-400 font-mono font-bold"
                                >
                                    {{ sub.amount }}
                                </span>
                            </div>

                            <div
                                class="text-[11px] text-slate-400 flex items-center justify-between"
                            >
                                <span>{{ sub.plan }}</span>
                                <span class="font-mono text-slate-500">
                                    {{ sub.phone }}
                                </span>
                            </div>

                            <div
                                class="pt-2 border-t border-slate-800/60 flex items-center justify-between"
                            >
                                <span
                                    class="text-[10px] text-slate-500 font-mono"
                                >
                                    المرجع: {{ sub.ref }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        @click="approve(sub.id)"
                                        class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-[10px] transition cursor-pointer"
                                    >
                                        اعتماد
                                    </button>
                                    <button
                                        type="button"
                                        @click="reject(sub.id)"
                                        class="px-2 py-1 bg-slate-800 hover:bg-rose-500/20 hover:text-rose-300 text-slate-400 rounded-lg text-[10px] transition cursor-pointer"
                                    >
                                        رفض
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
