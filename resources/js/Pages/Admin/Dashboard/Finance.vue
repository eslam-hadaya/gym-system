<script setup>
import { ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    CheckCircle2,
    XCircle,
    Info,
    Search,
    Check,
    X,
    Loader2,
    Wallet,
    CreditCard,
    Banknote,
    FileText,
} from "@lucide/vue";

const props = defineProps({
    transactions: Object,
    currentMethod: String,
    stats: Object,
});

const page = usePage();
const processingId = ref(null);
const actionType = ref(null);
const toastMessage = ref("");
const toastType = ref("success");

const showToast = (message, type = "success") => {
    toastMessage.value = message;
    toastType.value = type;
    setTimeout(() => {
        toastMessage.value = "";
    }, 4000);
};

const setFilter = (method) => {
    router.get(
        "/admin/dashboard/finance",
        { method: method === "all" ? undefined : method },
        { preserveState: true, replace: true },
    );
};

const handleVerify = (subId) => {
    if (
        !confirm(
            "هل أنت متأكد من استلام المبلغ واعتماد هذا الاشتراك وتفعيله فوراً؟",
        )
    )
        return;
    processingId.value = subId;
    actionType.value = "verify";
    router.post(
        `/admin/dashboard/finance/verify/${subId}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                showToast("تم اعتماد الدفعة وتفعيل العضوية بنجاح.", "success"),
            onError: (errors) =>
                showToast(Object.values(errors)[0] || "حدث خطأ.", "error"),
            onFinish: () => {
                processingId.value = null;
                actionType.value = null;
            },
        },
    );
};

const handleReject = (subId) => {
    if (!confirm("هل أنت متأكد من رفض طلب هذا الاشتراك؟")) return;
    processingId.value = subId;
    actionType.value = "reject";
    router.post(
        `/admin/dashboard/finance/reject/${subId}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                showToast("تم رفض طلب الاشتراك وإلغاء المعاملة.", "info"),
            onError: (errors) =>
                showToast(
                    Object.values(errors)[0] || "تعذر إتمام عملية الرفض.",
                    "error",
                ),
            onFinish: () => {
                processingId.value = null;
                actionType.value = null;
            },
        },
    );
};

const handleCancelActive = (subId, memberName) => {
    if (!confirm(`هل أنت متأكد من إلغاء هذا الاشتراك للاعب (${memberName})؟`))
        return;
    processingId.value = subId;
    actionType.value = "cancel";
    router.post(
        `/admin/dashboard/subscription/${subId}/cancel`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                showToast(`تم إلغاء اشتراك اللاعب (${memberName}).`, "info"),
            onError: (errors) =>
                showToast(
                    Object.values(errors)[0] || "تعذر إلغاء الاشتراك.",
                    "error",
                ),
            onFinish: () => {
                processingId.value = null;
                actionType.value = null;
            },
        },
    );
};

const formatPrice = (val) => {
    return new Intl.NumberFormat("ar-SY").format(val || 0) + " ل.س";
};
</script>

<template>
    <Head title="إدارة الصندوق ومطابقة المدفوعات" />

    <AdminLayout>
        <!-- Toast -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed bottom-6 left-6 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-medium text-white shadow-2xl"
                :class="{
                    'bg-success-500': toastType === 'success',
                    'bg-danger-500': toastType === 'error',
                    'bg-surface-elevated border border-line-strong text-ink-primary':
                        toastType === 'info',
                }"
            >
                <CheckCircle2
                    v-if="toastType === 'success'"
                    class="h-4 w-4"
                    :stroke-width="2.5"
                />
                <XCircle
                    v-else-if="toastType === 'error'"
                    class="h-4 w-4"
                    :stroke-width="2.5"
                />
                <Info v-else class="h-4 w-4" :stroke-width="2.5" />
                <span>{{ toastMessage }}</span>
            </div>
        </transition>

        <div class="space-y-6" dir="rtl">
            <!-- الترويسة والفلاتر -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-xl font-black tracking-tight text-ink-primary sm:text-2xl"
                    >
                        إدارة الصندوق ومطابقة الحوالات
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                        تدقيق ومطابقة إشعارات شام كاش والمقبوضات النقدية واعتماد
                        وإلغاء الاشتراكات
                    </p>
                </div>

                <div
                    class="inline-flex flex-wrap rounded-xl border border-line-soft bg-surface-elevated p-1"
                >
                    <button
                        @click="setFilter('all')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all sm:px-4 sm:py-2"
                        :class="
                            currentMethod === 'all' || !currentMethod
                                ? 'bg-brand-500 text-white shadow-sm'
                                : 'text-ink-secondary hover:text-ink-primary'
                        "
                    >
                        جميع المعاملات
                    </button>
                    <button
                        @click="setFilter('shamcash')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all sm:px-4 sm:py-2"
                        :class="
                            currentMethod === 'shamcash'
                                ? 'bg-brand-500 text-white shadow-sm'
                                : 'text-ink-secondary hover:text-brand-500'
                        "
                    >
                        حوالات شام كاش
                    </button>
                    <button
                        @click="setFilter('cash')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all sm:px-4 sm:py-2"
                        :class="
                            currentMethod === 'cash'
                                ? 'bg-brand-500 text-white shadow-sm'
                                : 'text-ink-secondary hover:text-brand-500'
                        "
                    >
                        المقبوضات النقدية
                    </button>
                </div>
            </div>

            <!-- الإحصائيات -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-2xl border border-line-soft bg-surface-elevated p-5 shadow-sm"
                >
                    <span class="text-xs font-bold text-ink-muted"
                        >إجمالي الدخل المعتمد</span
                    >
                    <p class="mt-1 text-xl font-black text-ink-primary">
                        {{ formatPrice(stats.total_income) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-success-500/20 bg-success-500/5 p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-bold text-success-600 dark:text-success-400"
                            >دخل شام كاش</span
                        >
                        <span
                            class="h-2 w-2 rounded-full bg-success-500"
                        ></span>
                    </div>
                    <p class="mt-1 text-xl font-black text-ink-primary">
                        {{ formatPrice(stats.shamcash_income) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-brand-500/20 bg-brand-500/5 p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-bold text-brand-600 dark:text-brand-400"
                            >المقبوضات النقدية</span
                        >
                        <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                    </div>
                    <p class="mt-1 text-xl font-black text-ink-primary">
                        {{ formatPrice(stats.cash_income) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-warning-500/20 bg-warning-500/5 p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-bold text-warning-600 dark:text-warning-400"
                            >طلبات قيد التدقيق</span
                        >
                        <span
                            class="rounded-full bg-warning-500/20 px-2 py-0.5 text-xs font-black text-warning-600 dark:text-warning-400"
                        >
                            {{ stats.pending_count || 0 }}
                        </span>
                    </div>
                    <p class="mt-2 text-[11px] text-ink-secondary">
                        تتطلب مطابقة واعتماد الكابتن
                    </p>
                </div>
            </div>

            <!-- الجدول -->
            <div
                class="overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead
                            class="border-b border-line-soft bg-surface-soft text-xs font-bold text-ink-secondary"
                        >
                            <tr>
                                <th class="p-3 sm:p-4">رقم المعاملة</th>
                                <th class="p-3 sm:p-4">اللاعب</th>
                                <th class="p-3 sm:p-4">الباقة</th>
                                <th class="p-3 sm:p-4">المبلغ</th>
                                <th class="p-3 sm:p-4">طريقة الدفع</th>
                                <th class="p-3 sm:p-4">رقم الإشعار</th>
                                <th class="p-3 sm:p-4">التاريخ</th>
                                <th class="p-3 sm:p-4">الحالة</th>
                                <th class="p-3 text-center sm:p-4">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-line-soft font-medium text-ink-primary"
                        >
                            <tr
                                v-for="item in transactions.data"
                                :key="item.subscription_id"
                                class="transition-colors hover:bg-glass-bg-hover"
                            >
                                <td
                                    class="p-3 font-mono text-[11px] font-bold text-ink-muted sm:p-4"
                                >
                                    {{ item.id }}
                                </td>
                                <td class="p-3 sm:p-4">
                                    <div class="font-bold">
                                        {{ item.member }}
                                    </div>
                                    <div
                                        class="mt-0.5 font-mono text-[10px] text-ink-muted"
                                    >
                                        {{ item.member_code }}
                                    </div>
                                </td>
                                <td class="p-3 sm:p-4">
                                    <span class="font-bold">{{
                                        item.plan_name
                                    }}</span>
                                    <span
                                        class="block text-[10px] text-ink-muted"
                                        >{{ item.plan_days }} يوماً</span
                                    >
                                </td>
                                <td class="p-3 font-black sm:p-4">
                                    {{ formatPrice(item.amount) }}
                                </td>
                                <td class="p-3 sm:p-4">
                                    <span
                                        v-if="item.method === 'shamcash'"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/20 bg-success-500/10 px-2.5 py-1 text-[10px] font-black text-success-600 dark:text-success-400"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-success-500"
                                        ></span>
                                        شام كاش
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-brand-500/20 bg-brand-500/10 px-2.5 py-1 text-[10px] font-black text-brand-600 dark:text-brand-400"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-brand-500"
                                        ></span>
                                        نقداً
                                    </span>
                                </td>
                                <td class="p-3 sm:p-4">
                                    <span
                                        v-if="item.method === 'shamcash'"
                                        class="select-all rounded-md border border-line-soft bg-surface-base px-2 py-1 font-mono text-[11px] font-black text-ink-primary"
                                    >
                                        {{ item.ref }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-[11px] text-ink-muted"
                                        >{{ item.ref }}</span
                                    >
                                </td>
                                <td
                                    class="p-3 font-mono text-[11px] text-ink-secondary sm:p-4"
                                >
                                    {{ item.date }}
                                </td>
                                <td class="p-3 sm:p-4">
                                    <span
                                        v-if="item.status === 'verified'"
                                        class="inline-flex rounded-md border border-success-500/20 bg-success-500/10 px-2.5 py-1 text-[10px] font-bold text-success-600 dark:text-success-400"
                                        >معتمد ومفعّل</span
                                    >
                                    <span
                                        v-else-if="item.status === 'pending'"
                                        class="inline-flex animate-pulse rounded-md border border-warning-500/20 bg-warning-500/10 px-2.5 py-1 text-[10px] font-bold text-warning-600 dark:text-warning-400"
                                        >قيد التدقيق</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex rounded-md border border-danger-500/20 bg-danger-500/10 px-2.5 py-1 text-[10px] font-bold text-danger-500"
                                        >مرفوض / ملغى</span
                                    >
                                </td>
                                <td class="p-3 text-center sm:p-4">
                                    <div
                                        v-if="item.status === 'pending'"
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <button
                                            @click="
                                                handleVerify(
                                                    item.subscription_id,
                                                )
                                            "
                                            :disabled="
                                                processingId ===
                                                item.subscription_id
                                            "
                                            class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-success-500 px-2.5 py-1.5 text-[10px] font-bold text-white shadow-sm transition-all hover:bg-success-600 disabled:opacity-50 sm:px-3 sm:text-xs"
                                        >
                                            <Loader2
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'verify'
                                                "
                                                class="h-3 w-3 animate-spin"
                                                :stroke-width="2.5"
                                            />
                                            <Check
                                                v-else
                                                class="h-3 w-3"
                                                :stroke-width="3"
                                            />
                                            اعتماد
                                        </button>
                                        <button
                                            @click="
                                                handleReject(
                                                    item.subscription_id,
                                                )
                                            "
                                            :disabled="
                                                processingId ===
                                                item.subscription_id
                                            "
                                            class="flex cursor-pointer items-center gap-1 rounded-lg border border-danger-500/20 bg-surface-base px-2.5 py-1.5 text-[10px] font-bold text-danger-500 transition-all hover:bg-danger-500/10 disabled:opacity-50"
                                        >
                                            <Loader2
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'reject'
                                                "
                                                class="h-3 w-3 animate-spin"
                                                :stroke-width="2.5"
                                            />
                                            <X
                                                v-else
                                                class="h-3 w-3"
                                                :stroke-width="3"
                                            />
                                            رفض
                                        </button>
                                    </div>
                                    <div
                                        v-else-if="item.status === 'verified'"
                                        class="flex items-center justify-center"
                                    >
                                        <button
                                            @click="
                                                handleCancelActive(
                                                    item.subscription_id,
                                                    item.member,
                                                )
                                            "
                                            :disabled="
                                                processingId ===
                                                item.subscription_id
                                            "
                                            class="flex cursor-pointer items-center gap-1 rounded-lg border border-danger-500/20 bg-danger-500/5 px-2.5 py-1.5 text-[10px] font-bold text-danger-500 transition-all hover:bg-danger-500/10 disabled:opacity-50 sm:px-3 sm:text-xs"
                                        >
                                            <Loader2
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'cancel'
                                                "
                                                class="h-3 w-3 animate-spin"
                                                :stroke-width="2.5"
                                            />
                                            إلغاء
                                        </button>
                                    </div>
                                    <span
                                        v-else
                                        class="text-[10px] font-medium text-ink-muted"
                                        >مكتمل</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td
                                    colspan="9"
                                    class="p-8 text-center text-sm text-ink-muted"
                                >
                                    لا توجد أي معاملات مسجلة حالياً.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
