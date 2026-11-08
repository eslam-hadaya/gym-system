<script setup>
import { ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    transactions: Object,
    currentMethod: String,
    stats: Object,
});

const page = usePage();
const processingId = ref(null);
const actionType = ref(null); // 'verify', 'reject', 'cancel'
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

// 1. اعتماد الدفعة وتفعيل العضوية
const handleVerify = (subId) => {
    if (
        !confirm(
            "هل أنت متأكد من استلام المبلغ واعتماد هذا الاشتراك وتفعيله فوراً؟",
        )
    ) {
        return;
    }

    processingId.value = subId;
    actionType.value = "verify";

    router.post(
        `/admin/dashboard/finance/verify/${subId}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    "تم اعتماد الدفعة وتفعيل العضوية بالتجديد التراكمي بنجاح.",
                    "success",
                );
            },
            onError: (errors) => {
                const msg =
                    Object.values(errors)[0] || "حدث خطأ أثناء اعتماد الدفعة.";
                showToast(msg, "error");
            },
            onFinish: () => {
                processingId.value = null;
                actionType.value = null;
            },
        },
    );
};

// 2. رفض طلب الاشتراك المعلق
const handleReject = (subId) => {
    if (!confirm("هل أنت متأكد من رفض طلب هذا الاشتراك؟")) {
        return;
    }

    processingId.value = subId;
    actionType.value = "reject";

    router.post(
        `/admin/dashboard/finance/reject/${subId}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast("تم رفض طلب الاشتراك وإلغاء المعاملة بنجاح.", "info");
            },
            onError: (errors) => {
                const msg =
                    Object.values(errors)[0] || "تعذر إتمام عملية الرفض.";
                showToast(msg, "error");
            },
            onFinish: () => {
                processingId.value = null;
                actionType.value = null;
            },
        },
    );
};

// 3. إلغاء اشتراك نشط ومعتمد من قبل الأدمن
const handleCancelActive = (subId, memberName) => {
    if (
        !confirm(
            `هل أنت متأكد من إلغاء هذا الاشتراك المعتمد للاعب (${memberName})؟ سيتم إيقاف صلاحية دخوله للصالة فوراً دون استرجاع مالي.`,
        )
    ) {
        return;
    }

    processingId.value = subId;
    actionType.value = "cancel";

    router.post(
        `/admin/dashboard/subscription/${subId}/cancel`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    `تم إلغاء اشتراك اللاعب (${memberName}) وإيقاف بطاقة الدخول.`,
                    "info",
                );
            },
            onError: (errors) => {
                const msg = Object.values(errors)[0] || "تعذر إلغاء الاشتراك.";
                showToast(msg, "error");
            },
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
        <!-- الإشعار المنبثق Toast -->
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
                class="fixed bottom-6 left-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-white text-sm font-medium"
                :class="{
                    'bg-emerald-600 border border-emerald-500':
                        toastType === 'success',
                    'bg-rose-600 border border-rose-500': toastType === 'error',
                    'bg-slate-800 border border-slate-700':
                        toastType === 'info',
                }"
            >
                <span v-if="toastType === 'success'">✓</span>
                <span v-else-if="toastType === 'error'">✕</span>
                <span v-else>ℹ</span>
                <span>{{ toastMessage }}</span>
            </div>
        </transition>

        <div class="space-y-6">
            <!-- ترويسة الصفحة والفلاتر -->
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-black text-slate-900 tracking-tight"
                    >
                        إدارة الصندوق ومطابقة الحوالات
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        تدقيق ومطابقة إشعارات شام كاش والمقبوضات النقدية واعتماد
                        وإلغاء الاشتراكات
                    </p>
                </div>

                <div
                    class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200"
                >
                    <button
                        type="button"
                        @click="setFilter('all')"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer',
                            currentMethod === 'all' || !currentMethod
                                ? 'bg-white text-slate-900 shadow-xs'
                                : 'text-slate-600 hover:text-slate-900',
                        ]"
                    >
                        جميع المعاملات
                    </button>
                    <button
                        type="button"
                        @click="setFilter('shamcash')"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer',
                            currentMethod === 'shamcash'
                                ? 'bg-emerald-600 text-white shadow-xs'
                                : 'text-slate-600 hover:text-emerald-700',
                        ]"
                    >
                        حوالات شام كاش
                    </button>
                    <button
                        type="button"
                        @click="setFilter('cash')"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer',
                            currentMethod === 'cash'
                                ? 'bg-sky-600 text-white shadow-xs'
                                : 'text-slate-600 hover:text-sky-700',
                        ]"
                    >
                        المقبوضات النقدية (كاش)
                    </button>
                </div>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div
                    class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs"
                >
                    <span class="text-xs font-bold text-slate-400"
                        >إجمالي الدخل المعتمد</span
                    >
                    <p class="text-xl font-black text-slate-900 mt-1">
                        {{ formatPrice(stats.total_income) }}
                    </p>
                </div>

                <div
                    class="bg-white p-5 rounded-2xl border border-emerald-100 bg-emerald-50/20 shadow-xs"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-700"
                            >دخل شام كاش الموثق</span
                        >
                        <span
                            class="w-2 h-2 rounded-full bg-emerald-500"
                        ></span>
                    </div>
                    <p class="text-xl font-black text-emerald-900 mt-1">
                        {{ formatPrice(stats.shamcash_income) }}
                    </p>
                </div>

                <div
                    class="bg-white p-5 rounded-2xl border border-sky-100 bg-sky-50/20 shadow-xs"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-700"
                            >المقبوضات النقدية المعتمدة</span
                        >
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <p class="text-xl font-black text-sky-900 mt-1">
                        {{ formatPrice(stats.cash_income) }}
                    </p>
                </div>

                <div
                    class="bg-white p-5 rounded-2xl border border-amber-200 bg-amber-50/30 shadow-xs"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-800"
                            >طلبات وحوالات قيد التدقيق</span
                        >
                        <span
                            class="px-2 py-0.5 text-xs font-black bg-amber-200 text-amber-900 rounded-full"
                        >
                            {{ stats.pending_count || 0 }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        تتطلب مطابقة واعتماد الكابتن
                    </p>
                </div>
            </div>

            <!-- جدول المعاملات -->
            <div
                class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-xs font-bold"
                        >
                            <tr>
                                <th class="p-4">رقم المعاملة</th>
                                <th class="p-4">اللاعب</th>
                                <th class="p-4">الباقة المختارة</th>
                                <th class="p-4">المبلغ</th>
                                <th class="p-4">طريقة الدفع</th>
                                <th class="p-4">رقم إشعار الحوالة (المرجع)</th>
                                <th class="p-4">تاريخ الطلب</th>
                                <th class="p-4">الحالة</th>
                                <th class="p-4 text-center">الإجراء المالي</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 text-slate-700 font-medium"
                        >
                            <tr
                                v-for="item in transactions.data"
                                :key="item.subscription_id"
                                class="hover:bg-slate-50/60 transition-colors"
                            >
                                <td
                                    class="p-4 text-xs font-mono text-slate-400 font-bold"
                                >
                                    {{ item.id }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-900">
                                        {{ item.member }}
                                    </div>
                                    <div
                                        class="text-xs text-slate-400 font-mono mt-0.5"
                                    >
                                        {{ item.member_code }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-slate-800">{{
                                        item.plan_name
                                    }}</span>
                                    <span class="text-xs text-slate-400 block"
                                        >{{ item.plan_days }} يوماً</span
                                    >
                                </td>
                                <td class="p-4 font-black text-slate-900">
                                    {{ formatPrice(item.amount) }}
                                </td>
                                <td class="p-4">
                                    <span
                                        v-if="item.method === 'shamcash'"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-emerald-500"
                                        ></span>
                                        شام كاش
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-sky-50 text-sky-700 border border-sky-200"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-sky-500"
                                        ></span>
                                        نقداً (كاش)
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        v-if="item.method === 'shamcash'"
                                        class="font-mono text-xs font-black px-2 py-1 bg-slate-100 border border-slate-200 text-slate-800 rounded-md select-all"
                                    >
                                        {{ item.ref }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-xs text-slate-400 font-medium"
                                    >
                                        {{ item.ref }}
                                    </span>
                                </td>
                                <td
                                    class="p-4 text-xs text-slate-500 font-mono"
                                >
                                    {{ item.date }}
                                </td>
                                <td class="p-4">
                                    <span
                                        v-if="item.status === 'verified'"
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    >
                                        معتمد ومفعّل
                                    </span>
                                    <span
                                        v-else-if="item.status === 'pending'"
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse"
                                    >
                                        قيد التدقيق
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200"
                                    >
                                        مرفوض / ملغى
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <!-- إجراءات الطلب المعلق: اعتماد أو رفض -->
                                    <div
                                        v-if="item.status === 'pending'"
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                handleVerify(
                                                    item.subscription_id,
                                                )
                                            "
                                            :disabled="
                                                processingId ===
                                                item.subscription_id
                                            "
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition-all disabled:opacity-50 flex items-center gap-1.5 cursor-pointer"
                                        >
                                            <span
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'verify'
                                                "
                                                class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"
                                            ></span>
                                            اعتماد
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                handleReject(
                                                    item.subscription_id,
                                                )
                                            "
                                            :disabled="
                                                processingId ===
                                                item.subscription_id
                                            "
                                            class="px-2.5 py-1.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold rounded-lg transition-all disabled:opacity-50 flex items-center gap-1 cursor-pointer"
                                        >
                                            <span
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'reject'
                                                "
                                                class="inline-block w-3 h-3 border-2 border-rose-600 border-t-transparent rounded-full animate-spin"
                                            ></span>
                                            رفض
                                        </button>
                                    </div>

                                    <!-- إجراء الاشتراك المعتمد: إلغاء الصلاحية من قبل الأدمن -->
                                    <div
                                        v-else-if="item.status === 'verified'"
                                        class="flex items-center justify-center"
                                    >
                                        <button
                                            type="button"
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
                                            class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-lg transition-all disabled:opacity-50 flex items-center gap-1 cursor-pointer"
                                        >
                                            <span
                                                v-if="
                                                    processingId ===
                                                        item.subscription_id &&
                                                    actionType === 'cancel'
                                                "
                                                class="inline-block w-3 h-3 border-2 border-rose-600 border-t-transparent rounded-full animate-spin"
                                            ></span>
                                            إلغاء الاشتراك
                                        </button>
                                    </div>

                                    <!-- العمليات المنتهية أو المرفوضة مسبقاً -->
                                    <span
                                        v-else
                                        class="text-xs text-slate-300 font-medium"
                                        >مكتمل</span
                                    >
                                </td>
                            </tr>

                            <tr v-if="transactions.data.length === 0">
                                <td
                                    colspan="9"
                                    class="p-8 text-center text-slate-400 text-sm"
                                >
                                    لا توجد أي معاملات مسجلة ضمن هذا التصنيف
                                    حالياً.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
