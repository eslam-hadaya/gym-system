<script setup>
import { ref, computed } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import MemberLayout from "@/Layouts/MemberLayout.vue";
import {
    useCountdown,
    useRemainingDays,
} from "@/Composables/useRemainingDays";

const props = defineProps({
    plans: Array,
    currentSubscription: Object,
    renewal: Object,
    memberStatus: String,
});

const remainingDays = useRemainingDays(
    () => props.renewal?.end_date,
);
const graceCountdown = useCountdown(
    () => props.renewal?.grace_expires_at,
);

const form = useForm({
    plan_id: props.plans && props.plans.length > 0 ? props.plans[0].id : null,
    payment_method: "shamcash",
    transaction_ref: "",
});

const submitError = ref("");
const submitSuccess = ref(false);
const showCancelModal = ref(false);
const isCancelling = ref(false);

// هل يسمح بالتجديد (آخر 10 أيام فقط إذا كان الاشتراك معتمداً)
const canRenew = computed(() => {
    return (
        !props.renewal?.has_active_subscription || remainingDays.value <= 10
    );
});

const selectedPlan = computed(() => {
    return props.plans?.find((p) => p.id === form.plan_id) || null;
});

// تأكيد طلب الاشتراك الجديد
const submitOrder = () => {
    submitError.value = "";
    submitSuccess.value = false;

    if (!canRenew.value) {
        submitError.value = `لا يمكنك التجديد حالياً، يتبقى لديك ${remainingDays.value} يوماً. التجديد متاح في آخر 10 أيام فقط.`;
        return;
    }

    if (!form.plan_id) {
        submitError.value = "يرجى تحديد باقة رياضية للمتابعة.";
        return;
    }

    if (form.payment_method === "shamcash" && !form.transaction_ref?.trim()) {
        submitError.value = "يرجى إدخال رقم إشعار عملية تحويل شام كاش.";
        return;
    }

    if (form.payment_method === "cash") {
        form.transaction_ref = "";
    }

    form.post("/member/dashboard/subscriptions", {
        preserveScroll: true,
        onSuccess: () => {
            submitSuccess.value = true;
            form.reset("transaction_ref");
        },
        onError: (errors) => {
            submitError.value =
                Object.values(errors)[0] ||
                "تعذر إتمام الطلب، تحقق من البيانات.";
        },
    });
};

// تنفيذ إلغاء الاشتراك
const confirmCancellation = () => {
    isCancelling.value = true;
    router.post(
        "/member/dashboard/subscriptions/cancel",
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showCancelModal.value = false;
            },
            onError: (errors) => {
                alert(errors.cancel || "تعذر إلغاء الاشتراك.");
            },
            onFinish: () => {
                isCancelling.value = false;
            },
        },
    );
};

const formatPrice = (val) => {
    return new Intl.NumberFormat("ar-SY").format(val || 0) + " ل.س";
};
</script>

<template>
    <Head title="إدارة الاشتراكات وتجديد الباقة" />

    <MemberLayout>
        <div class="max-w-5xl mx-auto space-y-8">
            <!-- ترويسة الصفحة -->
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    الاشتراكات وتجديد العضوية
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    اختر الباقة الرياضية المناسبة وأكد طريقة الدفع لتفعيل بطاقتك
                    الرقمية فوراً
                </p>
            </div>

            <div
                v-if="renewal?.is_in_grace_period"
                class="bg-rose-50 border-2 border-rose-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div>
                    <h2 class="font-black text-rose-900">
                        انتهى اشتراكك وأنت ضمن مهلة السماح
                    </h2>
                    <p class="text-xs text-rose-700 mt-1">
                        جدّد الآن؛ يتوقف الدخول عند انتهاء العداد.
                    </p>
                </div>
                <div
                    v-if="graceCountdown.isActive && !graceCountdown.isComplete"
                    class="flex items-center gap-2 font-mono font-black text-xl text-rose-700"
                    aria-live="polite"
                    aria-label="الوقت المتبقي من مهلة السماح"
                >
                    <span v-if="graceCountdown.days > 0">
                        {{ graceCountdown.days }} يوم
                    </span>
                    <span>{{ String(graceCountdown.hours).padStart(2, "0") }}</span>
                    <span>:</span>
                    <span>{{ String(graceCountdown.minutes).padStart(2, "0") }}</span>
                    <span>:</span>
                    <span>{{ String(graceCountdown.seconds).padStart(2, "0") }}</span>
                </div>
                <span v-else class="text-sm font-bold text-rose-700">
                    انتهت مهلة السماح؛ حدّث الصفحة لتحديث حالة الاشتراك.
                </span>
            </div>

            <!-- تنبيه منع التجديد المبكر -->
            <div
                v-if="!canRenew && renewal?.has_active_subscription"
                class="bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-5 flex items-start gap-4 text-amber-900"
            >
                <div
                    class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0 text-xl font-black"
                >
                    ⏳
                </div>
                <div>
                    <h3 class="font-black text-base text-amber-950">
                        التجديد غير متاح حالياً
                    </h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        يتبقى في اشتراكك الفعال
                        <span class="font-bold underline"
                            >{{ remainingDays }}
                            يوماً</span
                        >. وفقاً لنظام الصالة، يمكنك تقديم طلب تجديد الباقة فقط
                        عندما يتبقى في رصيدك
                        <span class="font-bold">10 أيام أو أقل</span>.
                    </p>
                </div>
            </div>

            <div
                v-else-if="renewal?.has_active_subscription"
                class="bg-emerald-500/10 border-2 border-emerald-500/30 rounded-2xl p-5 flex items-start gap-4 text-emerald-900"
            >
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0 text-xl font-black"
                >
                    ✓
                </div>
                <div>
                    <h3 class="font-black text-base text-emerald-950">
                        أصبح التجديد متاحاً
                    </h3>
                    <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                        تبقى {{ remainingDays }} أيام أو أقل من اشتراكك، ويمكنك
                        الآن تقديم طلب التجديد.
                    </p>
                </div>
            </div>

            <!-- صندوق حالة الاشتراك الحالي -->
            <div
                v-if="currentSubscription"
                class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400"
                            >حالة اشتراكك الحالي</span
                        >
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-black text-slate-900">
                                {{ currentSubscription.plan_name }}
                            </h2>

                            <!-- معتمد وسارٍ -->
                            <span
                                v-if="currentSubscription.status === 'verified'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black border"
                                :class="
                                    currentSubscription.is_in_grace_period
                                        ? 'bg-rose-50 text-rose-700 border-rose-200'
                                        : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                "
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"
                                ></span>
                                {{
                                    currentSubscription.is_in_grace_period
                                        ? "ضمن مهلة السماح"
                                        : `مفعّل وسارٍ (متبقي ${remainingDays} يوم)`
                                }}
                            </span>

                            <!-- قيد التدقيق -->
                            <span
                                v-else-if="
                                    currentSubscription.status === 'pending'
                                "
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200 animate-pulse"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-amber-500"
                                ></span>
                                قيد التدقيق والاعتماد
                            </span>

                            <!-- مرفوض من الإدارة -->
                            <span
                                v-else-if="
                                    currentSubscription.status === 'rejected'
                                "
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-rose-500"
                                ></span>
                                تم رفض الطلب من الإدارة
                            </span>

                            <!-- ملغى من قبل اللاعب -->
                            <span
                                v-else-if="
                                    currentSubscription.status === 'cancelled'
                                "
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-100 text-slate-700 border border-slate-200"
                            >
                                ملغى
                            </span>

                            <!-- منتهٍ -->
                            <span
                                v-else
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-100 text-slate-600"
                            >
                                منتهٍ
                            </span>
                        </div>
                    </div>

                    <!-- التواريخ وزر إلغاء الاشتراك -->
                    <div class="flex items-center gap-6 text-sm text-slate-600">
                        <div>
                            <span class="text-xs text-slate-400 block font-bold"
                                >تاريخ البداية</span
                            >
                            <span class="font-mono font-bold">{{
                                currentSubscription.start_date
                            }}</span>
                        </div>
                        <div class="border-r border-slate-200 pr-6">
                            <span class="text-xs text-slate-400 block font-bold"
                                >تاريخ الانتهاء</span
                            >
                            <span class="font-mono font-bold text-slate-900">{{
                                currentSubscription.end_date
                            }}</span>
                        </div>
                        <div
                            v-if="
                                currentSubscription.status === 'verified' ||
                                currentSubscription.status === 'pending'
                            "
                            class="border-r border-slate-200 pr-6"
                        >
                            <button
                                type="button"
                                @click="showCancelModal = true"
                                class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            >
                                إلغاء الاشتراك
                            </button>
                        </div>
                    </div>
                </div>

                <!-- إشعار توضيحي إذا تم الرفض -->
                <div
                    v-if="currentSubscription.status === 'rejected'"
                    class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium"
                >
                    تم رفض طلب اشتراكك الأخير من قبل موظف الاستقبال أو الإدارة.
                    يرجى مراجعة الصالة لتسليم المبلغ نقداً أو التحقق من صحة
                    إشعار شام كاش وإعادة المحاولة.
                </div>
            </div>

            <!-- اختيار الباقات -->
            <div class="space-y-4">
                <h2 class="text-lg font-black text-slate-900">
                    1. اختر الباقة المناسبة
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="form.plan_id = plan.id"
                        :class="[
                            'cursor-pointer p-6 rounded-2xl border-2 transition-all relative overflow-hidden',
                            form.plan_id === plan.id
                                ? 'border-emerald-600 bg-emerald-50/20 shadow-md'
                                : 'border-slate-200 hover:border-slate-300 bg-white',
                        ]"
                    >
                        <div
                            v-if="form.plan_id === plan.id"
                            class="absolute top-4 left-4 w-6 h-6 bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-xs"
                        >
                            <svg
                                class="w-3.5 h-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>

                        <span
                            class="text-xs font-black text-slate-400 uppercase tracking-wider block mb-1"
                        >
                            {{ plan.days }} يوماً تمرين
                        </span>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ plan.name }}
                        </h3>
                        <p class="text-2xl font-black text-emerald-700 mt-4">
                            {{ formatPrice(plan.price) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            تجديد تراكمي يُضاف مباشرة إلى رصيدك
                        </p>
                    </div>
                </div>

                <div
                    v-if="form.errors.plan_id"
                    class="text-xs font-bold text-rose-600"
                >
                    {{ form.errors.plan_id }}
                </div>
            </div>

            <!-- طريقة الدفع وتثبيت الطلب -->
            <div class="space-y-4">
                <h2 class="text-lg font-black text-slate-900">
                    2. حدد وسيلة الدفع
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- شام كاش -->
                    <div
                        @click="form.payment_method = 'shamcash'"
                        :class="[
                            'cursor-pointer p-5 rounded-2xl border-2 transition-all flex items-start gap-4',
                            form.payment_method === 'shamcash'
                                ? 'border-emerald-600 bg-emerald-50/20 shadow-xs'
                                : 'border-slate-200 hover:border-slate-300 bg-white',
                        ]"
                    >
                        <div
                            class="w-5 h-5 mt-0.5 rounded-full border-2 flex items-center justify-center"
                            :class="
                                form.payment_method === 'shamcash'
                                    ? 'border-emerald-600'
                                    : 'border-slate-300'
                            "
                        >
                            <div
                                v-if="form.payment_method === 'shamcash'"
                                class="w-2.5 h-2.5 bg-emerald-600 rounded-full"
                            ></div>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">
                                شام كاش (تحويل إلكتروني)
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">
                                التحويل الفوري عبر تطبيق شام كاش مع إرفاق رقم
                                العملية
                            </p>
                        </div>
                    </div>

                    <!-- كاش -->
                    <div
                        @click="form.payment_method = 'cash'"
                        :class="[
                            'cursor-pointer p-5 rounded-2xl border-2 transition-all flex items-start gap-4',
                            form.payment_method === 'cash'
                                ? 'border-sky-600 bg-sky-50/20 shadow-xs'
                                : 'border-slate-200 hover:border-slate-300 bg-white',
                        ]"
                    >
                        <div
                            class="w-5 h-5 mt-0.5 rounded-full border-2 flex items-center justify-center"
                            :class="
                                form.payment_method === 'cash'
                                    ? 'border-sky-600'
                                    : 'border-slate-300'
                            "
                        >
                            <div
                                v-if="form.payment_method === 'cash'"
                                class="w-2.5 h-2.5 bg-sky-600 rounded-full"
                            ></div>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">
                                نقداً في الصالة (كاش)
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">
                                حجز الباقة وتسليم المبلغ يداً بيد لكابتن
                                الاستقبال
                            </p>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل شام كاش -->
                <div
                    v-if="form.payment_method === 'shamcash'"
                    class="bg-slate-900 text-white rounded-2xl p-6 space-y-4"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4"
                    >
                        <div>
                            <span
                                class="text-xs text-emerald-400 font-bold block"
                            >
                                رقم حساب شام كاش المعتمد للصالة
                            </span>
                            <p
                                class="text-xl font-mono font-black mt-0.5 tracking-wider text-slate-100 select-all"
                            >
                                0932642429
                            </p>
                        </div>
                        <div class="text-left">
                            <span class="text-xs text-slate-400 block"
                                >المبلغ المطلوب تحويله</span
                            >
                            <span class="text-lg font-black text-emerald-400">
                                {{ formatPrice(selectedPlan?.price) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-300">
                            رقم إشعار عملية التحويل (TXN Reference / رقم
                            الإشعار)
                        </label>
                        <input
                            v-model="form.transaction_ref"
                            type="text"
                            placeholder="مثال: 948271038"
                            class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all placeholder:text-slate-500"
                        />
                        <p class="text-xs text-slate-400">
                            ستجد رقم العملية داخل تطبيق شام كاش في تفاصيل
                            الإشعار بعد إتمام التحويل.
                        </p>
                        <div
                            v-if="form.errors.transaction_ref"
                            class="text-xs font-bold text-rose-400 mt-1"
                        >
                            {{ form.errors.transaction_ref }}
                        </div>
                    </div>
                </div>

                <!-- تعليمات الكاش -->
                <div
                    v-else
                    class="bg-sky-50 border border-sky-200 rounded-2xl p-5 text-sky-900 space-y-1"
                >
                    <h4 class="font-bold text-sm">تعليمات الدفع النقدي</h4>
                    <p class="text-xs text-sky-700 leading-relaxed">
                        عند تأكيد الطلب، سيتم تسجيل حجز الباقة بقيمة
                        <span class="font-black">{{
                            formatPrice(selectedPlan?.price)
                        }}</span
                        >، يرجى مراجعة موظف الاستقبال في الصالة لتسديد المبلغ
                        نقداً وتفعيل بطاقة الدخول فوراً.
                    </p>
                </div>

                <!-- التنبيهات -->
                <div
                    v-if="submitError"
                    class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold rounded-xl"
                >
                    {{ submitError }}
                </div>

                <div
                    v-if="submitSuccess"
                    class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold rounded-xl"
                >
                    تم تسجيل طلب الاشتراك بنجاح! راجع موظف الاستقبال لتسليم
                    المبلغ وتفعيل البطاقة.
                </div>

                <!-- زر التثبيت -->
                <div class="pt-4 flex justify-end">
                    <button
                        type="button"
                        @click="submitOrder"
                        :disabled="form.processing || !canRenew"
                        :class="[
                            'w-full sm:w-auto px-8 py-3.5 text-white font-black text-sm rounded-xl transition-all flex items-center justify-center gap-2',
                            canRenew && !form.processing
                                ? 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-lg shadow-emerald-900/20 cursor-pointer'
                                : 'bg-slate-400 cursor-not-allowed opacity-60',
                        ]"
                    >
                        <span
                            v-if="form.processing"
                            class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
                        ></span>
                        <span v-if="!canRenew"
                            >التجديد غير متاح (متبقي أكثر من 10 أيام)</span
                        >
                        <span v-else>تأكيد طلب الاشتراك الآن</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- نافذة التأكيد المنبثقة لإلغاء الاشتراك (Modal) -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
        >
            <div
                class="bg-white rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl border border-slate-200 space-y-6 animate-in fade-in zoom-in-95 duration-200"
            >
                <div
                    class="w-14 h-14 rounded-2xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center text-2xl mx-auto font-black"
                >
                    ⚠️
                </div>

                <div class="text-center space-y-2">
                    <h3 class="text-xl font-black text-slate-900">
                        هل أنت متأكد من إلغاء الاشتراك؟
                    </h3>
                    <div
                        class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs text-right leading-relaxed font-medium"
                    >
                        <strong class="font-black block mb-1"
                            >تنبيه هام ومشدد:</strong
                        >
                        وفقاً للسياسة المالية للصالات الرياضية،
                        <span class="font-black underline"
                            >لن يتم استرجاع أي جزء من المبلغ المدفوع
                            إطلاقاً</span
                        >، وسيتم إيقاف صلاحية الدخول عبر بطاقتك فوراً.
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="confirmCancellation"
                        :disabled="isCancelling"
                        class="flex-1 py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-xs transition-all shadow-md cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span
                            v-if="isCancelling"
                            class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"
                        ></span>
                        نعم، إلغاء الاشتراك نهائياً
                    </button>
                    <button
                        type="button"
                        @click="showCancelModal = false"
                        :disabled="isCancelling"
                        class="py-3 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-all cursor-pointer"
                    >
                        تراجع
                    </button>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
