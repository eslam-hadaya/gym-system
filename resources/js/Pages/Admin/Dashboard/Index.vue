<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    ScanLine,
    Users,
    Clock,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    ShieldAlert,
    ArrowRight,
    UserPlus,
} from "@lucide/vue";

const props = defineProps({
    stats: { type: Array, required: true },
    recentCheckIns: { type: Array, required: true },
    pendingSubscriptions: { type: Array, required: true },
    expiringSoonSubscriptions: { type: Array, default: () => [] },
});

const approve = (id) => {
    router.post(
        `/admin/dashboard/subscription/${id}/approve`,
        {},
        { preserveScroll: true },
    );
};

const reject = (id) => {
    router.post(
        `/admin/dashboard/subscription/${id}/reject`,
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="لوحة التحكم الرئيسية - الإدارة" />

    <AdminLayout>
        <div class="space-y-6 sm:space-y-8" dir="rtl">
            <!-- الترويسة -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="text-xl font-black tracking-tight text-ink-primary sm:text-2xl"
                    >
                        نظرة عامة على الصالة
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                        متابعة التدفق الحي لدخول اللاعبين والعمليات المالية
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/admin/dashboard/checkin"
                        class="flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-400"
                    >
                        <ScanLine class="h-4 w-4" :stroke-width="2.5" />
                        <span>فتح كشك الاستقبال</span>
                    </Link>
                    <Link
                        href="/admin/dashboard/members"
                        class="flex items-center gap-2 rounded-xl border border-line-strong bg-surface-elevated px-4 py-2.5 text-xs font-semibold text-ink-primary transition hover:bg-glass-bg-hover"
                    >
                        <UserPlus class="h-4 w-4" :stroke-width="2" />
                        <span>المشتركين</span>
                    </Link>
                </div>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, idx) in stats"
                    :key="idx"
                    class="group relative overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated p-5 shadow-sm transition-all hover:border-brand-500/30"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-ink-secondary">{{
                            stat.label
                        }}</span>
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-xl border border-line-strong bg-glass-bg text-base text-ink-secondary group-hover:text-brand-500 transition-colors"
                        >
                            {{ stat.icon }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span
                            class="font-mono text-2xl font-black tracking-tight text-ink-primary"
                            >{{ stat.value }}</span
                        >
                        <span
                            :class="['text-[11px] font-semibold', stat.color]"
                            >{{ stat.change }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- تنبيهات الاشتراكات -->
            <div
                v-if="expiringSoonSubscriptions.length > 0"
                class="space-y-4 rounded-2xl border border-warning-500/30 bg-warning-500/5 p-4 sm:p-6"
            >
                <div
                    class="flex items-center justify-between border-b border-warning-500/20 pb-4"
                >
                    <div class="flex items-center gap-2.5">
                        <span
                            class="h-2.5 w-2.5 rounded-full bg-warning-500 animate-ping"
                        ></span>
                        <h2
                            class="text-sm font-bold text-warning-600 dark:text-warning-400"
                        >
                            تنبيهات الاشتراكات (قرب الانتهاء / مهلة السماح)
                        </h2>
                    </div>
                    <span
                        class="rounded-md border border-warning-500/20 bg-warning-500/10 px-2.5 py-1 font-mono text-[11px] font-bold text-warning-600 dark:text-warning-400"
                        >{{ expiringSoonSubscriptions.length }} لاعبين</span
                    >
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead
                            class="border-b border-warning-500/20 font-semibold text-ink-muted"
                        >
                            <tr>
                                <th class="px-2 py-3">اللاعب</th>
                                <th class="px-2 py-3">كود العضوية</th>
                                <th class="px-2 py-3">الباقة</th>
                                <th class="px-2 py-3">تاريخ الانتهاء</th>
                                <th class="px-2 py-3 text-center">
                                    الأيام المتبقية
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-line-soft text-ink-primary"
                        >
                            <tr
                                v-for="sub in expiringSoonSubscriptions"
                                :key="sub.id"
                                class="transition-colors hover:bg-glass-bg-hover"
                            >
                                <td class="px-2 py-3.5">
                                    <div
                                        class="flex flex-wrap items-center gap-2 font-bold"
                                    >
                                        <span>{{ sub.member_name }}</span>
                                        <span
                                            v-if="sub.alert_status === 'grace'"
                                            class="rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] text-danger-500"
                                            >مهلة السماح</span
                                        >
                                        <span
                                            v-else
                                            class="rounded-md border border-warning-500/20 bg-warning-500/10 px-2 py-0.5 text-[10px] text-warning-600 dark:text-warning-400"
                                            >قرب الانتهاء</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-2 py-3.5 font-mono text-brand-600 dark:text-brand-400"
                                >
                                    {{ sub.member_code }}
                                </td>
                                <td class="px-2 py-3.5 text-ink-secondary">
                                    {{ sub.plan_name }}
                                </td>
                                <td
                                    class="px-2 py-3.5 font-mono text-ink-secondary"
                                >
                                    {{ sub.end_date }}
                                </td>
                                <td class="px-2 py-3.5 text-center">
                                    <span
                                        class="inline-block rounded-md px-2.5 py-1 text-[10px] font-bold"
                                        :class="
                                            sub.alert_status === 'grace'
                                                ? 'border border-danger-500/20 bg-danger-500/10 text-danger-500'
                                                : sub.remaining_days === 0
                                                  ? 'border border-danger-500/20 bg-danger-500/10 text-danger-500'
                                                  : 'border border-warning-500/20 bg-warning-500/10 text-warning-600 dark:text-warning-400'
                                        "
                                    >
                                        {{
                                            sub.alert_status === "grace"
                                                ? "ضمن مهلة السماح"
                                                : sub.remaining_days === 0
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

            <!-- الشبكة السفلية -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- سجل الباب الحي -->
                <div
                    class="space-y-4 rounded-2xl border border-line-soft bg-surface-elevated p-4 shadow-sm sm:p-6 lg:col-span-2"
                >
                    <div
                        class="flex items-center justify-between border-b border-line-soft pb-4"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-success-500 animate-pulse"
                            ></span>
                            <h2 class="text-sm font-bold text-ink-primary">
                                آخر حركات الدخول
                            </h2>
                        </div>
                        <span
                            class="font-mono text-[10px] text-ink-muted sm:text-[11px]"
                            >سجل الحضور الفعلي</span
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead
                                class="border-b border-line-soft font-semibold text-ink-muted"
                            >
                                <tr>
                                    <th class="px-2 py-3">اللاعب</th>
                                    <th class="px-2 py-3">كود العضوية</th>
                                    <th class="px-2 py-3">الباقة</th>
                                    <th class="px-2 py-3">الوقت</th>
                                    <th class="px-2 py-3">حالة الباب</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-line-soft text-ink-primary"
                            >
                                <tr v-if="recentCheckIns.length === 0">
                                    <td
                                        colspan="5"
                                        class="py-8 text-center text-ink-muted"
                                    >
                                        لا توجد حركات دخول مسجلة بعد
                                    </td>
                                </tr>
                                <tr
                                    v-for="entry in recentCheckIns"
                                    :key="entry.id"
                                    class="transition-colors hover:bg-glass-bg-hover"
                                >
                                    <td
                                        class="flex items-center gap-2 px-2 py-3.5 font-bold"
                                    >
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-lg bg-glass-bg text-[11px] text-brand-500"
                                        >
                                            <UserIcon
                                                class="h-3.5 w-3.5"
                                                :stroke-width="2"
                                            />
                                        </span>
                                        <span>{{ entry.name }}</span>
                                    </td>
                                    <td
                                        class="px-2 py-3.5 font-mono text-ink-secondary"
                                    >
                                        {{ entry.code }}
                                    </td>
                                    <td class="px-2 py-3.5 text-ink-secondary">
                                        {{ entry.plan }}
                                    </td>
                                    <td class="px-2 py-3.5 text-ink-muted">
                                        {{ entry.time }}
                                    </td>
                                    <td class="px-2 py-3.5">
                                        <span
                                            v-if="entry.status === 'valid'"
                                            class="inline-flex items-center gap-1 rounded-md border border-success-500/20 bg-success-500/10 px-2 py-0.5 text-[10px] font-bold text-success-600 dark:text-success-400"
                                        >
                                            <CheckCircle2
                                                class="h-3 w-3"
                                                :stroke-width="2.5"
                                            />
                                            مسموح
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] font-bold text-danger-500"
                                        >
                                            <XCircle
                                                class="h-3 w-3"
                                                :stroke-width="2.5"
                                            />
                                            غير مسموح
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- طلبات الاعتماد -->
                <div
                    class="space-y-4 rounded-2xl border border-line-soft bg-surface-elevated p-4 shadow-sm sm:p-6"
                >
                    <div
                        class="flex items-center justify-between border-b border-line-soft pb-4"
                    >
                        <h2 class="text-sm font-bold text-ink-primary">
                            بانتظار الاعتماد
                        </h2>
                        <span
                            class="rounded-md bg-warning-500/10 px-2 py-0.5 text-[10px] font-bold text-warning-600 dark:text-warning-400"
                            >{{ pendingSubscriptions.length }} معلقة</span
                        >
                    </div>

                    <div class="space-y-3">
                        <div
                            v-if="pendingSubscriptions.length === 0"
                            class="py-8 text-center text-xs text-ink-muted"
                        >
                            لا توجد طلبات معلقة بانتظار الاعتماد
                        </div>

                        <div
                            v-for="sub in pendingSubscriptions"
                            :key="sub.id"
                            class="space-y-2.5 rounded-xl border border-line-soft bg-surface-base p-3 text-xs sm:p-4"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-ink-primary">{{
                                    sub.name
                                }}</span>
                                <span
                                    class="font-mono font-bold text-brand-600 dark:text-brand-400"
                                    >{{ sub.amount }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between text-[10px] text-ink-secondary sm:text-[11px]"
                            >
                                <span>{{ sub.plan }}</span>
                                <span class="font-mono">{{ sub.phone }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-t border-line-soft pt-2"
                            >
                                <span
                                    class="font-mono text-[10px] text-ink-muted"
                                    >المرجع: {{ sub.ref }}</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="approve(sub.id)"
                                        class="cursor-pointer rounded-lg bg-success-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-success-600"
                                    >
                                        اعتماد
                                    </button>
                                    <button
                                        @click="reject(sub.id)"
                                        class="cursor-pointer rounded-lg border border-line-soft bg-surface-elevated px-2 py-1 text-[10px] text-ink-muted transition hover:border-danger-500/30 hover:bg-danger-500/10 hover:text-danger-500"
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
