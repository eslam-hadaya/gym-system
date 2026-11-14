<script setup>
import { ref, onMounted, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import MemberLayout from "@/Layouts/MemberLayout.vue";
import QRCode from "qrcode";
import { useCountdown, useRemainingDays } from "@/Composables/useRemainingDays";
import {
    Clock,
    AlertTriangle,
    CheckCircle2,
    CalendarDays,
    History,
    Loader2,
    Download,
    Dumbbell,
} from "@lucide/vue";

const props = defineProps({
    member: { type: Object, default: () => ({}) },
    subscription: { type: Object, default: null },
    recentCheckIns: { type: Array, default: () => [] },
    hasCheckedInToday: { type: Boolean, default: false },
    hasPendingCheckIn: { type: Boolean, default: false },
});

const remainingDays = useRemainingDays(() => props.subscription?.end_date);
const graceCountdown = useCountdown(() => props.subscription?.grace_expires_at);

const qrCanvas = ref(null);

const generateQR = async () => {
    if (!props.member?.member_code || !qrCanvas.value) return;
    try {
        await QRCode.toCanvas(
            qrCanvas.value,
            String(props.member.member_code),
            {
                width: 200,
                margin: 2,
                color: { dark: "#0f172a", light: "#ffffff" },
                errorCorrectionLevel: "H",
            },
        );
    } catch (err) {
        console.error("Failed to generate QR code:", err);
    }
};

const downloadQR = () => {
    if (!qrCanvas.value) return;
    try {
        const imageUri = qrCanvas.value.toDataURL("image/png");
        const downloadLink = document.createElement("a");
        downloadLink.href = imageUri;
        downloadLink.download = `QR_${props.member?.member_code || "code"}.png`;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    generateQR();
});
watch(
    () => props.member?.member_code,
    () => {
        generateQR();
    },
);

const isRequestingCheckIn = ref(false);

const requestManualCheckIn = () => {
    if (
        isRequestingCheckIn.value ||
        props.hasCheckedInToday ||
        props.hasPendingCheckIn
    )
        return;
    isRequestingCheckIn.value = true;
    router.post(
        "/member/dashboard/checkin-request",
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                alert(
                    "تم إرسال طلب الدخول بنجاح! يرجى إبلاغ موظف الاستقبال لاعتماده.",
                );
            },
            onError: (errors) => {
                const msg =
                    errors.checkin ||
                    Object.values(errors)[0] ||
                    "تعذر إرسال طلب الدخول.";
                alert(msg);
            },
            onFinish: () => {
                isRequestingCheckIn.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="لوحة تحكم اللاعب" />

    <MemberLayout>
        <div class="space-y-6" dir="rtl">
            <!-- ============ شريط التنبيهات ============ -->
            <div
                v-if="subscription?.is_in_grace_period"
                class="flex flex-col justify-between gap-3 rounded-2xl border border-danger-500/30 bg-danger-500/10 p-4 sm:flex-row sm:items-center sm:p-5"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-danger-500/20 text-danger-500"
                    >
                        <Clock class="h-4 w-4" :stroke-width="2.5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-danger-500">
                            انتهى الاشتراك وأنت الآن ضمن مهلة السماح
                        </h3>
                        <p class="mt-0.5 text-xs text-danger-500/80">
                            ينتهي السماح عند وصول العداد للصفر، وبعدها يتوقف
                            الدخول. جدّد اشتراكك الآن.
                        </p>
                    </div>
                </div>
                <div
                    v-if="graceCountdown.isActive && !graceCountdown.isComplete"
                    class="flex items-center gap-1.5 font-mono text-base font-black text-danger-500"
                    aria-live="polite"
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
                <span v-else class="text-xs font-bold text-danger-500"
                    >انتهت مهلة السماح؛ حدّث الصفحة.</span
                >
            </div>

            <div
                v-else-if="subscription && remainingDays <= 5"
                class="flex flex-col justify-between gap-3 rounded-2xl border border-warning-500/30 bg-warning-500/10 p-4 sm:flex-row sm:items-center sm:p-5"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warning-500/20 text-warning-600 dark:text-warning-400"
                    >
                        <AlertTriangle class="h-4 w-4" :stroke-width="2.5" />
                    </span>
                    <div>
                        <h3
                            class="text-sm font-black text-warning-600 dark:text-warning-400"
                        >
                            تنبيه: اقتراب موعد انتهاء الاشتراك
                        </h3>
                        <p
                            class="mt-0.5 text-xs text-warning-600/80 dark:text-warning-400/80"
                        >
                            يتبقى في باقتك الحالية
                            <span class="font-bold font-mono"
                                >{{ remainingDays }}
                                {{ remainingDays === 1 ? "يوم" : "أيام" }}</span
                            >
                            فقط (ينتهي في {{ subscription.end_date }}).
                        </p>
                    </div>
                </div>
                <Link
                    href="/member/dashboard/subscriptions"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-warning-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-warning-600 sm:w-auto"
                >
                    تجديد الاشتراك الآن
                </Link>
            </div>

            <!-- ============ ترويسة الترحيب ============ -->
            <div
                class="flex flex-col justify-between gap-3 rounded-2xl border border-line-soft bg-surface-elevated p-5 sm:flex-row sm:items-center sm:p-6"
            >
                <div>
                    <h2
                        class="text-base font-black text-ink-primary sm:text-lg"
                    >
                        أهلاً بك يا كابتن، {{ member?.name || "اللاعب" }}
                    </h2>
                    <p class="mt-0.5 text-xs text-ink-secondary">
                        {{
                            member?.gender === "female"
                                ? "فترة التدريب: صباحية (سيدات)"
                                : "فترة التدريب: مسائية (رجال)"
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-ink-muted">حالة الحساب:</span>
                    <span
                        v-if="subscription"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold"
                        :class="
                            subscription.is_in_grace_period
                                ? 'border-danger-500/30 bg-danger-500/10 text-danger-500'
                                : 'border-success-500/30 bg-success-500/10 text-success-600 dark:text-success-400'
                        "
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="
                                subscription.is_in_grace_period
                                    ? 'bg-danger-500'
                                    : 'bg-success-500'
                            "
                        ></span>
                        {{
                            subscription.is_in_grace_period
                                ? "ضمن مهلة السماح"
                                : "نشط ومصرح بالدخول"
                        }}
                    </span>
                    <span
                        v-else
                        class="rounded-lg border border-warning-500/30 bg-warning-500/10 px-2.5 py-1 text-[11px] font-bold text-warning-600 dark:text-warning-400"
                    >
                        بانتظار الاشتراك
                    </span>
                </div>
            </div>

            <!-- ============ الشبكة الرئيسية ============ -->
            <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-12">
                <!-- بطاقة الدخول الرقمية (اليسار) -->
                <div class="md:col-span-5">
                    <div
                        class="relative overflow-hidden rounded-3xl border border-line-soft bg-surface-elevated p-5 shadow-sm sm:p-6"
                    >
                        <div
                            class="flex items-center justify-between border-b border-line-soft pb-4"
                        >
                            <div>
                                <span
                                    class="block text-[11px] font-mono font-bold text-ink-muted"
                                    >رقم العضوية</span
                                >
                                <span
                                    class="text-base font-mono font-black tracking-wider text-brand-600 dark:text-brand-400"
                                >
                                    {{ member?.member_code || "—" }}
                                </span>
                            </div>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-[10px] font-black border"
                                :class="
                                    subscription
                                        ? 'bg-success-500/10 text-success-600 dark:text-success-400 border-success-500/20'
                                        : 'bg-warning-500/10 text-warning-600 dark:text-warning-400 border-warning-500/20'
                                "
                            >
                                {{
                                    subscription
                                        ? subscription.plan_name
                                        : "غير مشترك"
                                }}
                            </span>
                        </div>

                        <!-- رمز QR -->
                        <div
                            class="my-6 flex flex-col items-center justify-center"
                        >
                            <div
                                class="rounded-2xl border border-line-soft bg-white p-3 shadow-sm"
                            >
                                <canvas
                                    ref="qrCanvas"
                                    class="block rounded-xl"
                                ></canvas>
                            </div>
                            <span
                                class="mt-2 font-mono text-xs font-bold tracking-widest text-ink-secondary"
                            >
                                {{ member?.member_code }}
                            </span>
                        </div>

                        <!-- أزرار التحكم -->
                        <div
                            class="mt-4 space-y-3 border-t border-line-soft pt-4"
                        >
                            <div v-if="subscription">
                                <div
                                    v-if="hasCheckedInToday"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-success-500/30 bg-success-500/10 px-3 py-2.5 text-xs font-bold text-success-600 dark:text-success-400"
                                >
                                    <CheckCircle2
                                        class="h-4 w-4"
                                        :stroke-width="2.5"
                                    />
                                    <span
                                        >تم تسجيل حضورك اليوم بنجاح! نراك غداً
                                        كابتن.</span
                                    >
                                </div>

                                <div
                                    v-else-if="hasPendingCheckIn"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-warning-500/30 bg-warning-500/10 px-3 py-2.5 text-xs font-bold text-warning-600 dark:text-warning-400"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-warning-500 animate-pulse"
                                    ></span>
                                    <span
                                        >طلب الدخول بانتظار موافقة
                                        الاستقبال...</span
                                    >
                                </div>

                                <button
                                    v-else
                                    type="button"
                                    @click="requestManualCheckIn"
                                    :disabled="isRequestingCheckIn"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-brand-400 disabled:opacity-50"
                                >
                                    <Loader2
                                        v-if="isRequestingCheckIn"
                                        class="h-3.5 w-3.5 animate-spin"
                                        :stroke-width="2.5"
                                    />
                                    <Dumbbell
                                        v-else
                                        class="h-4 w-4"
                                        :stroke-width="2.5"
                                    />
                                    <span>طلب تسجيل الدخول للتمرين الآن</span>
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="downloadQR"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-line-strong bg-surface-base px-4 py-2.5 text-xs font-bold text-ink-primary transition hover:bg-glass-bg-hover"
                            >
                                <Download class="h-4 w-4" :stroke-width="2" />
                                تنزيل رمز الـ QR للدخول
                            </button>

                            <Link
                                href="/member/dashboard/subscriptions"
                                class="block w-full rounded-xl border border-line-strong bg-surface-base px-4 py-2.5 text-center text-xs font-bold text-ink-primary transition hover:bg-glass-bg-hover"
                            >
                                {{
                                    subscription
                                        ? "تجديد / تمديد الباقة"
                                        : "الاشتراك في باقة الآن"
                                }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- التفاصيل (اليمين) -->
                <div class="space-y-6 md:col-span-7">
                    <!-- تفاصيل الاشتراك -->
                    <div
                        class="rounded-3xl border border-line-soft bg-surface-elevated p-5 sm:p-6"
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="flex items-center gap-2 text-sm font-bold text-ink-primary"
                            >
                                <CalendarDays
                                    class="h-4 w-4 text-brand-500"
                                    :stroke-width="2"
                                />
                                <span>تفاصيل الاشتراك الحالي</span>
                            </h3>
                            <span
                                v-if="subscription"
                                class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400"
                            >
                                ينتهي في: {{ subscription.end_date }}
                            </span>
                        </div>

                        <div
                            v-if="subscription"
                            class="mt-4 grid grid-cols-2 gap-4"
                        >
                            <div
                                class="rounded-2xl border border-line-soft bg-surface-base p-4"
                            >
                                <span
                                    class="block text-[11px] text-ink-muted mb-1"
                                    >الباقة النشطة</span
                                >
                                <span
                                    class="text-sm font-bold text-ink-primary"
                                    >{{ subscription.plan_name }}</span
                                >
                            </div>
                            <div
                                class="rounded-2xl border border-line-soft bg-surface-base p-4"
                            >
                                <span
                                    class="block text-[11px] text-ink-muted mb-1"
                                    >الأيام المتبقية</span
                                >
                                <span
                                    class="font-mono text-xl font-black text-brand-600 dark:text-brand-400"
                                >
                                    {{ remainingDays }} يوم
                                </span>
                            </div>

                            <div
                                v-if="subscription.is_in_grace_period"
                                class="col-span-2 flex flex-col justify-between gap-2 rounded-2xl border border-danger-500/30 bg-danger-500/10 p-4 sm:flex-row sm:items-center"
                            >
                                <span class="text-xs font-bold text-danger-500"
                                    >الوقت المتبقي قبل إيقاف الدخول</span
                                >
                                <span
                                    v-if="
                                        graceCountdown.isActive &&
                                        !graceCountdown.isComplete
                                    "
                                    class="font-mono text-lg font-black text-danger-500"
                                >
                                    <span v-if="graceCountdown.days > 0"
                                        >{{ graceCountdown.days }} يوم
                                    </span>
                                    {{
                                        String(graceCountdown.hours).padStart(
                                            2,
                                            "0",
                                        )
                                    }}:
                                    {{
                                        String(graceCountdown.minutes).padStart(
                                            2,
                                            "0",
                                        )
                                    }}:
                                    {{
                                        String(graceCountdown.seconds).padStart(
                                            2,
                                            "0",
                                        )
                                    }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs font-bold text-danger-500"
                                    >انتهت مهلة السماح.</span
                                >
                            </div>
                        </div>

                        <div
                            v-else
                            class="mt-4 rounded-2xl border border-dashed border-line-strong bg-surface-base p-6 text-center"
                        >
                            <p class="text-xs text-ink-secondary">
                                ليس لديك باقة تدريب نشطة حالياً
                            </p>
                            <Link
                                href="/member/dashboard/subscriptions"
                                class="mt-3 inline-block text-xs font-bold text-brand-600 hover:underline dark:text-brand-400"
                            >
                                تصفح الباقات واشترك الآن ←
                            </Link>
                        </div>
                    </div>

                    <!-- سجل الحضور -->
                    <div
                        class="rounded-3xl border border-line-soft bg-surface-elevated p-5 sm:p-6"
                    >
                        <h3
                            class="mb-4 flex items-center gap-2 text-sm font-bold text-ink-primary"
                        >
                            <History
                                class="h-4 w-4 text-brand-500"
                                :stroke-width="2"
                            />
                            <span>آخر حركات تسجيل الدخول بالصالة</span>
                        </h3>

                        <div v-if="recentCheckIns.length > 0" class="space-y-2">
                            <div
                                v-for="checkIn in recentCheckIns"
                                :key="checkIn.id"
                                class="flex items-center justify-between rounded-xl border border-line-soft bg-surface-base p-3 text-xs"
                            >
                                <span class="font-mono text-ink-secondary">{{
                                    checkIn.created_at
                                }}</span>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                    :class="
                                        checkIn.status === 'valid'
                                            ? 'bg-success-500/10 text-success-600 dark:text-success-400 border border-success-500/20'
                                            : 'bg-danger-500/10 text-danger-500 border border-danger-500/20'
                                    "
                                >
                                    {{
                                        checkIn.status === "valid"
                                            ? "دخول نظامي"
                                            : "مرفوض"
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="py-6 text-center text-xs text-ink-muted"
                        >
                            لا توجد حركات تسجيل دخول مسجلة بعد
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
