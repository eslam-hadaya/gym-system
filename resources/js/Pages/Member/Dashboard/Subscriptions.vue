<script setup>
import { ref, computed } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import MemberLayout from "@/Layouts/MemberLayout.vue";
import { useCountdown, useRemainingDays } from "@/Composables/useRemainingDays";
import {
    Clock,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    ShieldCheck,
    Wallet,
    Smartphone,
    Loader2,
    X,
} from "@lucide/vue";

const props = defineProps({
    plans: Array,
    currentSubscription: Object,
    renewal: Object,
    memberStatus: String,
});

const remainingDays = useRemainingDays(() => props.renewal?.end_date);
const graceCountdown = useCountdown(() => props.renewal?.grace_expires_at);

const form = useForm({
    plan_id: props.plans && props.plans.length > 0 ? props.plans[0].id : null,
    payment_method: "shamcash",
    transaction_ref: "",
});

const submitError = ref("");
const submitSuccess = ref(false);
const showCancelModal = ref(false);
const isCancelling = ref(false);

const canRenew = computed(() => {
    return !props.renewal?.has_active_subscription || remainingDays.value <= 10;
});

const selectedPlan = computed(() => {
    return props.plans?.find((p) => p.id === form.plan_id) || null;
});

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
                Object.values(errors)[0] || "تعتم الطلب، تحقق من البيانات.";
        },
    });
};

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
        <div class="max-w-5xl mx-auto space-y-6 sm:space-y-8" dir="rtl">
            <!-- ترويسة الصفحة -->
            <div>
                <h1 class="text-xl font-black text-ink-primary sm:text-2xl">
                    الاشتراكات وتجديد العضوية
                </h1>
                <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                    اختر الباقة الرياضية المناسبة وأكد طريقة الدفع لتفعيل بطاقتك
                    الرقمية فوراً
                </p>
            </div>

            <!-- تنبيه مهلة السماح -->
            <div
                v-if="renewal?.is_in_grace_period"
                class="flex flex-col justify-between gap-4 rounded-2xl border-2 border-danger-500/30 bg-danger-500/5 p-4 sm:flex-row sm:items-center sm:p-5"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-danger-500/20 text-danger-500"
                    >
                        <Clock class="h-4 w-4" :stroke-width="2.5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-danger-500">
                            انتهى اشتراكك وأنت ضمن مهلة السماح
                        </h2>
                        <p class="mt-1 text-xs text-danger-500/80">
                            جدّد الآن؛ يتوقف الدخول عند انتهاء العداد.
                        </p>
                    </div>
                </div>
                <div
                    v-if="graceCountdown.isActive && !graceCountdown.isComplete"
                    class="flex items-center gap-1.5 font-mono text-lg font-black text-danger-500"
                    aria-live="polite"
                    aria-label="الوقت المتبقي من مهلة السماح"
                >
                    <span v-if="graceCountdown.days > 0"
                        >{{ graceCountdown.days }} ي
                    </span>
                    <span>{{
                        String(graceCountdown.hours).padStart(2, "0")
                    }}</span>:
                    <span>{{
                        String(graceCountdown.minutes).padStart(2, "0")
                    }}</span>:
                    <span>{{
                        String(graceCountdown.seconds).padStart(2, "0")
                    }}</span>
                </div>
                <span v-else class="text-xs font-bold text-danger-500">
                    انتهت مهلة السماح؛ حدّث الصفحة.
                </span>
            </div>

            <!-- تنبيه التجديد المبكر -->
            <div
                v-if="!canRenew && renewal?.has_active_subscription"
                class="flex items-start gap-4 rounded-2xl border border-warning-500/30 bg-warning-500/10 p-4 sm:p-5"
            >
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warning-500/20 text-warning-600 dark:text-warning-400"
                >
                    <AlertTriangle class="h-4 w-4" :stroke-width="2.5" />
                </span>
                <div>
                    <h3
                        class="text-sm font-bold text-warning-600 dark:text-warning-400"
                    >
                        التجديد غير متاح حالياً
                    </h3>
                    <p
                        class="mt-1 text-xs leading-relaxed text-warning-600/80 dark:text-warning-400/80"
                    >
                        يتبقى في اشتراكك الفعال
                        <span class="font-bold underline"
                            >{{ remainingDays }} يوماً</span
                        >. وفقاً للنظام، التجديد متاح فقط في آخر 10 أيام.
                    </p>
                </div>
            </div>

            <div
                v-else-if="renewal?.has_active_subscription"
                class="flex items-start gap-4 rounded-2xl border border-success-500/30 bg-success-500/10 p-4 sm:p-5"
            >
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-success-500/20 text-success-600 dark:text-success-400"
                >
                    <CheckCircle2 class="h-4 w-4" :stroke-width="2.5" />
                </span>
                <div>
                    <h3
                        class="text-sm font-bold text-success-600 dark:text-success-400"
                    >
                        أصبح التجديد متاحاً
                    </h3>
                    <p
                        class="mt-1 text-xs leading-relaxed text-success-600/80 dark:text-success-400/80"
                    >
                        تبقى {{ remainingDays }} أيام أو أقل من اشتراكك، ويمكنك
                        الآن تقديم طلب التجديد.
                    </p>
                </div>
            </div>

            <!-- صندوق حالة الاشتراك الحالي -->
            <div
                v-if="currentSubscription"
                class="rounded-2xl border border-line-soft bg-surface-elevated p-5 sm:p-6"
            >
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-ink-muted"
                            >حالة اشتراكك الحالي</span
                        >
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-black text-ink-primary">
                                {{ currentSubscription.plan_name }}
                            </h2>

                            <span
                                v-if="currentSubscription.status === 'verified'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-black"
                                :class="
                                    currentSubscription.is_in_grace_period
                                        ? 'border-danger-500/30 bg-danger-500/10 text-danger-500'
                                        : 'border-success-500/30 bg-success-500/10 text-success-600 dark:text-success-400'
                                "
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full"
                                    :class="
                                        currentSubscription.is_in_grace_period
                                            ? 'bg-danger-500'
                                            : 'bg-success-500'
                                    "
                                ></span>
                                {{
                                    currentSubscription.is_in_grace_period
                                        ? "ضمن مهلة السماح"
                                        : `مفعّل وسارٍ (متبقي ${remainingDays} يوم)`
                                }}
                            </span>

                            <span
                                v-else-if="
                                    currentSubscription.status === 'pending'
                                "
                                class="inline-flex items-center gap-1.5 rounded-full border border-warning-500/30 bg-warning-500/10 px-2.5 py-0.5 text-xs font-black text-warning-600 dark:text-warning-400"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-warning-500"
                                ></span>
                                قيد التدقيق والاعتماد
                            </span>

                            <span
                                v-else-if="
                                    currentSubscription.status === 'rejected'
                                "
                                class="inline-flex items-center gap-1.5 rounded-full border border-danger-500/30 bg-danger-500/10 px-2.5 py-0.5 text-xs font-black text-danger-500"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-danger-500"
                                ></span>
                                تم رفض الطلب من الإدارة
                            </span>

                            <span
                                v-else-if="
                                    currentSubscription.status === 'cancelled'
                                "
                                class="inline-flex items-center rounded-full border border-line-strong bg-glass-bg px-2.5 py-0.5 text-xs font-black text-ink-muted"
                            >
                                ملغى
                            </span>

                            <span
                                v-else
                                class="inline-flex items-center rounded-full border border-line-strong bg-glass-bg px-2.5 py-0.5 text-xs font-black text-ink-muted"
                            >
                                منتهٍ
                            </span>
                        </div>
                    </div>

                    <!-- التواريخ -->
                    <div
                        class="flex flex-wrap items-center gap-4 text-xs sm:gap-6"
                    >
                        <div>
                            <span
                                class="block text-[10px] font-bold text-ink-muted"
                                >تاريخ البداية</span
                            >
                            <span
                                class="font-mono font-bold text-ink-primary"
                                >{{ currentSubscription.start_date }}</span
                            >
                        </div>
                        <div class="border-r border-line-soft pr-4 sm:pr-6">
                            <span
                                class="block text-[10px] font-bold text-ink-muted"
                                >تاريخ الانتهاء</span
                            >
                            <span
                                class="font-mono font-bold text-ink-primary"
                                >{{ currentSubscription.end_date }}</span
                            >
                        </div>
                        <div
                            v-if="
                                currentSubscription.status === 'verified' ||
                                currentSubscription.status === 'pending'
                            "
                            class="border-r border-line-soft pr-4 sm:pr-6"
                        >
                            <button
                                type="button"
                                @click="showCancelModal = true"
                                class="rounded-xl border border-danger-500/30 bg-danger-500/10 px-3 py-1.5 text-xs font-bold text-danger-500 transition hover:bg-danger-500/20"
                            >
                                إلغاء الاشتراك
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="currentSubscription.status === 'rejected'"
                    class="mt-4 rounded-xl border border-danger-500/30 bg-danger-500/10 p-3 text-xs font-medium text-danger-500"
                >
                    تم رفض طلب اشتراكك الأخير من قبل الإدارة. يرجى مراجعة الصالة
                    لتسليم المبلغ نقداً أو التحقق من صحة إشعار شام كاش وإعادة
                    المحاولة.
                </div>
            </div>

            <!-- اختيار الباقات -->
            <div class="space-y-4">
                <h2 class="text-base font-black text-ink-primary sm:text-lg">
                    1. اختر الباقة المناسبة
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="form.plan_id = plan.id"
                        class="relative cursor-pointer overflow-hidden rounded-2xl border-2 p-5 transition-all duration-200 sm:p-6"
                        :class="
                            form.plan_id === plan.id
                                ? 'border-brand-500 bg-brand-500/5 shadow-sm'
                                : 'border-line-soft bg-surface-elevated hover:border-line-strong'
                        "
                    >
                        <div
                            v-if="form.plan_id === plan.id"
                            class="absolute left-4 top-4 flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm"
                        >
                            <CheckCircle2
                                class="h-3.5 w-3.5"
                                :stroke-width="3"
                            />
                        </div>

                        <span
                            class="mb-1 block text-[10px] font-black uppercase tracking-wider text-ink-muted"
                        >
                            {{ plan.days }} يوماً تمرين
                        </span>
                        <h3
                            class="text-lg font-black text-ink-primary sm:text-xl"
                        >
                            {{ plan.name }}
                        </h3>
                        <p
                            class="mt-3 text-xl font-black text-brand-600 dark:text-brand-400 sm:mt-4 sm:text-2xl"
                        >
                            {{ formatPrice(plan.price) }}
                        </p>
                        <p class="mt-1 text-[11px] text-ink-secondary">
                            تجديد تراكمي يُضاف مباشرة إلى رصيدك
                        </p>
                    </div>
                </div>

                <div
                    v-if="form.errors.plan_id"
                    class="text-xs font-bold text-danger-500"
                >
                    {{ form.errors.plan_id }}
                </div>
            </div>

            <!-- طريقة الدفع -->
            <div class="space-y-4">
                <h2 class="text-base font-black text-ink-primary sm:text-lg">
                    2. حدد وسيلة الدفع
                </h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- شام كاش -->
                    <div
                        @click="form.payment_method = 'shamcash'"
                        class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-4 transition-all duration-200 sm:p-5"
                        :class="
                            form.payment_method === 'shamcash'
                                ? 'border-brand-500 bg-brand-500/5'
                                : 'border-line-soft bg-surface-elevated hover:border-line-strong'
                        "
                    >
                        <div
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            :class="
                                form.payment_method === 'shamcash'
                                    ? 'border-brand-500'
                                    : 'border-line-strong'
                            "
                        >
                            <div
                                v-if="form.payment_method === 'shamcash'"
                                class="h-2.5 w-2.5 rounded-full bg-brand-500"
                            ></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-ink-primary">
                                شام كاش (تحويل إلكتروني)
                            </h4>
                            <p
                                class="mt-1 text-[11px] text-ink-secondary sm:text-xs"
                            >
                                التحويل الفوري عبر تطبيق شام كاش مع إرفاق رقم
                                العملية
                            </p>
                        </div>
                    </div>

                    <!-- كاش -->
                    <div
                        @click="form.payment_method = 'cash'"
                        class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-4 transition-all duration-200 sm:p-5"
                        :class="
                            form.payment_method === 'cash'
                                ? 'border-brand-500 bg-brand-500/5'
                                : 'border-line-soft bg-surface-elevated hover:border-line-strong'
                        "
                    >
                        <div
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            :class="
                                form.payment_method === 'cash'
                                    ? 'border-brand-500'
                                    : 'border-line-strong'
                            "
                        >
                            <div
                                v-if="form.payment_method === 'cash'"
                                class="h-2.5 w-2.5 rounded-full bg-brand-500"
                            ></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-ink-primary">
                                نقداً في الصالة (كاش)
                            </h4>
                            <p
                                class="mt-1 text-[11px] text-ink-secondary sm:text-xs"
                            >
                                حجز الباقة وتسليم المبلغ يداً بيد لكابتن
                                الاستقبال
                            </p>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل شام كاش -->
                <div
                    v-if="form.payment_method === 'shamcash'"
                    class="space-y-4 rounded-2xl border border-line-soft bg-surface-elevated p-5 sm:p-6"
                >
                    <div
                        class="flex flex-col justify-between gap-2 border-b border-line-soft pb-4 sm:flex-row sm:items-center"
                    >
                        <div>
                            <span
                                class="block text-[10px] font-bold text-brand-600 dark:text-brand-400"
                            >
                                رقم حساب شام كاش المعتمد للصالة
                            </span>
                            <p
                                class="mt-0.5 select-all font-mono text-lg font-black tracking-wider text-ink-primary"
                            >
                                0932642429
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] text-ink-muted"
                                >المبلغ المطلوب تحويله</span
                            >
                            <span
                                class="text-base font-black text-brand-600 dark:text-brand-400"
                            >
                                {{ formatPrice(selectedPlan?.price) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-bold text-ink-secondary"
                        >
                            رقم إشعار عملية التحويل (TXN Reference)
                        </label>
                        <input
                            v-model="form.transaction_ref"
                            type="text"
                            placeholder="مثال: 948271038"
                            class="w-full rounded-xl border border-line-strong bg-surface-base px-4 py-3 font-mono text-sm text-ink-primary placeholder:text-ink-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-300"
                        />
                        <p class="text-[11px] text-ink-secondary">
                            ستجد رقم العملية داخل تطبيق شام كاش في تفاصيل
                            الإشعار.
                        </p>
                        <div
                            v-if="form.errors.transaction_ref"
                            class="text-xs font-bold text-danger-500"
                        >
                            {{ form.errors.transaction_ref }}
                        </div>
                    </div>
                </div>

                <!-- تعليمات الكاش -->
                <div
                    v-else
                    class="space-y-1 rounded-2xl border border-brand-500/20 bg-brand-500/5 p-4 sm:p-5"
                >
                    <h4
                        class="text-sm font-bold text-brand-600 dark:text-brand-400"
                    >
                        تعليمات الدفع النقدي
                    </h4>
                    <p class="text-xs leading-relaxed text-ink-secondary">
                        عند تأكيد الطلب، سيتم تسجيل حجز الباقة بقيمة
                        <span class="font-black text-ink-primary">{{
                            formatPrice(selectedPlan?.price)
                        }}</span>، يرجى مراجعة موظف الاستقبال في الصالة لتسديد
                        المبلغ نقداً وتفعيل بطاقة الدخول فوراً.
                    </p>
                </div>

                <!-- التنبيهات -->
                <div
                    v-if="submitError"
                    class="rounded-xl border border-danger-500/30 bg-danger-500/10 p-4 text-sm font-bold text-danger-500"
                >
                    {{ submitError }}
                </div>

                <div
                    v-if="submitSuccess"
                    class="rounded-xl border border-success-500/30 bg-success-500/10 p-4 text-sm font-bold text-success-600 dark:text-success-400"
                >
                    تم تسجيل طلب الاشتراك بنجاح! راجع موظف الاستقبال لتسليم
                    المبلغ وتفعيل البطاقة.
                </div>

                <!-- زر التثبيت -->
                <div class="flex justify-end pt-2 sm:pt-4">
                    <button
                        type="button"
                        @click="submitOrder"
                        :disabled="form.processing || !canRenew"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-black transition-all duration-200 sm:w-auto sm:px-8"
                        :class="
                            canRenew && !form.processing
                                ? 'bg-brand-500 text-white shadow-glow hover:bg-brand-400 active:bg-brand-600'
                                : 'cursor-not-allowed bg-glass-bg text-ink-muted'
                        "
                    >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                            :stroke-width="2.5"
                        />
                        <span v-if="!canRenew">التجديد غير متاح</span>
                        <span v-else>تأكيد طلب الاشتراك الآن</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal الإلغاء -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                @click="showCancelModal = false"
            ></div>
            <div
                class="relative w-full max-w-md space-y-5 rounded-3xl border border-line-soft bg-surface-elevated p-6 shadow-2xl sm:space-y-6 sm:p-8"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-danger-500/30 bg-danger-500/10 text-danger-500"
                >
                    <AlertTriangle class="h-6 w-6" :stroke-width="2.5" />
                </div>

                <div class="space-y-2 text-center">
                    <h3 class="text-lg font-black text-ink-primary sm:text-xl">
                        هل أنت متأكد من إلغاء الاشتراك؟
                    </h3>
                    <div
                        class="rounded-2xl border border-danger-500/20 bg-danger-500/5 p-4 text-right text-xs font-medium leading-relaxed text-danger-500"
                    >
                        <strong class="mb-1 block font-black"
                            >تنبيه هام:</strong
                        >
                        وفقاً للسياسة المالية،
                        <span class="font-black underline"
                            >لن يتم استرجاع أي جزء من المبلغ المدفوع</span
                        >، وسيتم إيقاف صلاحية الدخول فوراً.
                    </div>
                </div>

                <div class="flex flex-col items-center gap-2 sm:flex-row">
                    <button
                        type="button"
                        @click="confirmCancellation"
                        :disabled="isCancelling"
                        class="inline-flex w-full flex-1 items-center justify-center gap-2 rounded-xl bg-danger-500 px-4 py-3 text-xs font-black text-white transition-all hover:bg-danger-600 disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isCancelling"
                            class="h-3.5 w-3.5 animate-spin"
                            :stroke-width="2.5"
                        />
                        نعم، إلغاء الاشتراك نهائياً
                    </button>
                    <button
                        type="button"
                        @click="showCancelModal = false"
                        :disabled="isCancelling"
                        class="w-full rounded-xl bg-glass-bg px-5 py-3 text-xs font-bold text-ink-secondary transition-all hover:bg-glass-bg-hover sm:w-auto"
                    >
                        تراجع
                    </button>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
