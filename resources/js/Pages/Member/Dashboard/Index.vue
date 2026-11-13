<script setup>
import { ref, onMounted, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import MemberLayout from "@/Layouts/MemberLayout.vue";
import QRCode from "qrcode";
import {
    useCountdown,
    useRemainingDays,
} from "@/Composables/useRemainingDays";

const props = defineProps({
    member: {
        type: Object,
        default: () => ({}),
    },
    subscription: {
        type: Object,
        default: null,
    },
    recentCheckIns: {
        type: Array,
        default: () => [],
    },
    hasCheckedInToday: {
        type: Boolean,
        default: false,
    },
    hasPendingCheckIn: {
        type: Boolean,
        default: false,
    },
});

const remainingDays = useRemainingDays(
    () => props.subscription?.end_date,
);
const graceCountdown = useCountdown(
    () => props.subscription?.grace_expires_at,
);

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
                color: {
                    dark: "#0f172a",
                    light: "#ffffff",
                },
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
        <div class="space-y-6">
            <!-- شريط تنبيه اقتراب انتهاء الاشتراك (آخر 5 أيام) -->
            <div
                v-if="subscription?.is_in_grace_period"
                class="bg-rose-500/10 border-2 border-rose-500/30 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
            >
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-xl shrink-0"
                    >
                        ⏳
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-rose-200">
                            انتهى الاشتراك وأنت الآن ضمن مهلة السماح
                        </h3>
                        <p class="text-xs text-rose-300/80 mt-0.5">
                            ينتهي السماح عند وصول العداد للصفر، وبعدها يتوقف
                            الدخول. جدّد اشتراكك الآن.
                        </p>
                    </div>
                </div>

                <div
                    v-if="graceCountdown.isActive && !graceCountdown.isComplete"
                    class="flex items-center gap-2 font-mono font-black text-rose-200"
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
                <span v-else class="text-xs font-bold text-rose-300">
                    انتهت مهلة السماح؛ حدّث الصفحة لتحديث حالة الاشتراك.
                </span>
            </div>

            <div
                v-else-if="subscription && remainingDays <= 5"
                class="bg-amber-500/10 border-2 border-amber-500/30 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
            >
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-xl shrink-0"
                    >
                        ⚠️
                    </div>
                    <div>
                        <h3
                            class="text-sm font-black text-amber-950 dark:text-amber-200"
                        >
                            تنبيه: اقتراب موعد انتهاء الاشتراك
                        </h3>
                        <p
                            class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5"
                        >
                            يتبقى في باقتك الحالية
                            <span
                                class="font-bold font-mono text-amber-600 dark:text-amber-400"
                            >
                                {{ remainingDays }}
                                {{
                                    remainingDays === 1
                                        ? "يوم"
                                        : "أيام"
                                }}
                            </span>
                            فقط (ينتهي في {{ subscription.end_date }}). يرجى
                            التجديد لتفادي انقطاع صلاحية الدخول.
                        </p>
                    </div>
                </div>

                <Link
                    href="/member/dashboard/subscriptions"
                    class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-950 font-black text-xs rounded-xl text-center transition-all shadow-sm shrink-0 cursor-pointer"
                >
                    تجديد الاشتراك الآن ←
                </Link>
            </div>

            <!-- ترويسة الترحيب وحالة الحساب -->
            <div
                class="bg-gradient-to-r from-emerald-500/15 via-slate-900 to-slate-900 p-6 rounded-3xl border border-emerald-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div>
                    <h2 class="text-lg font-black text-white">
                        أهلاً بك يا كابتن، {{ member?.name || "اللاعب" }} 👋
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        {{
                            member?.gender === "female"
                                ? "فترة التدريب: صباحية (سيدات)"
                                : "فترة التدريب: مسائية (رجال)"
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">حالة الحساب:</span>
                    <span
                        v-if="subscription"
                        class="text-xs font-bold px-2.5 py-1 rounded-lg border flex items-center gap-1.5"
                        :class="
                            subscription.is_in_grace_period
                                ? 'text-rose-300 bg-rose-500/10 border-rose-500/20'
                                : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
                        "
                    >
                        <span
                            class="w-2 h-2 rounded-full animate-pulse"
                            :class="
                                subscription.is_in_grace_period
                                    ? 'bg-rose-400'
                                    : 'bg-emerald-500'
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
                        class="text-xs font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20"
                    >
                        بانتظار الاشتراك / التفعيل
                    </span>
                </div>
            </div>

            <!-- تفاصيل البطاقة الرقمية والنشاط -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <!-- بطاقة الدخول الرقمية (اليسار) -->
                <div class="md:col-span-5">
                    <div
                        class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white rounded-3xl p-6 shadow-2xl border border-slate-700/60 relative overflow-hidden"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-700/60 pb-4"
                        >
                            <div>
                                <span
                                    class="text-[11px] font-mono font-bold text-slate-400 block"
                                    >رقم العضوية</span
                                >
                                <span
                                    class="text-base font-mono font-black text-emerald-400 tracking-wider"
                                >
                                    {{ member?.member_code || "—" }}
                                </span>
                            </div>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-black"
                                :class="
                                    subscription
                                        ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'
                                        : 'bg-amber-500/20 text-amber-300 border border-amber-500/40'
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
                                class="p-3 bg-white rounded-2xl shadow-xl border-4 border-slate-700/80"
                            >
                                <canvas
                                    ref="qrCanvas"
                                    class="rounded-xl block"
                                ></canvas>
                            </div>
                            <span
                                class="text-xs text-slate-400 mt-2 font-mono font-bold tracking-widest"
                            >
                                {{ member?.member_code }}
                            </span>
                        </div>

                        <!-- قسم زر الدخول والتنبيه الذكي -->
                        <div
                            class="mt-4 pt-4 border-t border-slate-800 space-y-3"
                        >
                            <div v-if="subscription">
                                <div
                                    v-if="hasCheckedInToday"
                                    class="w-full py-2.5 px-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl text-center flex items-center justify-center gap-2"
                                >
                                    <span>✅</span>
                                    <span
                                        >تم تسجيل حضورك اليوم بنجاح! نراك غداً
                                        كابتن.</span
                                    >
                                </div>

                                <div
                                    v-else-if="hasPendingCheckIn"
                                    class="w-full py-2 px-3 bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold rounded-xl text-center flex items-center justify-center gap-2"
                                >
                                    <span
                                        class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"
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
                                    class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl font-black text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                                >
                                    <span
                                        v-if="isRequestingCheckIn"
                                        class="inline-block w-3.5 h-3.5 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"
                                    ></span>
                                    <span v-else>🏋️</span>
                                    <span>طلب تسجيل الدخول للتمرين الآن</span>
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="downloadQR"
                                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-emerald-950/40"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    />
                                </svg>
                                تنزيل رمز الـ QR للدخول
                            </button>

                            <Link
                                href="/member/dashboard/subscriptions"
                                class="block w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-xl font-bold text-xs transition-all text-center"
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

                <!-- إحصائيات الاشتراك وسجل الحضور (اليمين) -->
                <div class="md:col-span-7 space-y-6">
                    <!-- تفاصيل الاشتراك الحالي -->
                    <div
                        class="bg-slate-900/60 border border-slate-800 p-6 rounded-3xl space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-sm font-bold text-white flex items-center gap-2"
                            >
                                <span>📅</span>
                                <span>تفاصيل الاشتراك الحالي</span>
                            </h3>
                            <span
                                v-if="subscription"
                                class="text-xs font-mono font-bold text-emerald-400"
                            >
                                ينتهي في: {{ subscription.end_date }}
                            </span>
                        </div>

                        <div
                            v-if="subscription"
                            class="grid grid-cols-2 gap-4 pt-2"
                        >
                            <div
                                class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] text-slate-400 block mb-1"
                                    >الباقة النشطة</span
                                >
                                <span class="text-sm font-bold text-white">{{
                                    subscription.plan_name
                                }}</span>
                            </div>
                            <div
                                class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] text-slate-400 block mb-1"
                                    >الأيام المتبقية</span
                                >
                                <span
                                    class="text-xl font-black text-emerald-400 font-mono"
                                >
                                    {{ remainingDays }} يوم
                                </span>
                            </div>
                            <div
                                v-if="subscription.is_in_grace_period"
                                class="col-span-2 flex flex-col gap-2 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 sm:flex-row sm:items-center sm:justify-between"
                                role="status"
                                aria-live="polite"
                            >
                                <span class="text-xs font-bold text-rose-200">
                                    الوقت المتبقي قبل إيقاف الدخول
                                </span>
                                <span
                                    v-if="
                                        graceCountdown.isActive &&
                                        !graceCountdown.isComplete
                                    "
                                    class="font-mono text-lg font-black text-rose-300"
                                >
                                    <span v-if="graceCountdown.days > 0">
                                        {{ graceCountdown.days }} يوم
                                    </span>
                                    {{ String(graceCountdown.hours).padStart(2, "0") }}:{{
                                        String(graceCountdown.minutes).padStart(
                                            2,
                                            "0",
                                        )
                                    }}:{{ String(graceCountdown.seconds).padStart(2, "0") }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs font-bold text-rose-300"
                                >
                                    انتهت مهلة السماح؛ حدّث الصفحة لتحديث حالة الاشتراك.
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="p-6 text-center rounded-2xl bg-slate-950/50 border border-dashed border-slate-800"
                        >
                            <p class="text-xs text-slate-400">
                                ليس لديك باقة تدريب نشطة حالياً
                            </p>
                            <Link
                                href="/member/dashboard/subscriptions"
                                class="inline-block mt-3 text-xs font-bold text-emerald-400 hover:underline"
                            >
                                تصفح الباقات واشترك الآن ←
                            </Link>
                        </div>
                    </div>

                    <!-- آخر حركات الدخول -->
                    <div
                        class="bg-slate-900/60 border border-slate-800 p-6 rounded-3xl space-y-4"
                    >
                        <h3
                            class="text-sm font-bold text-white flex items-center gap-2"
                        >
                            <span>⏱️</span>
                            <span>آخر حركات تسجيل الدخول بالصالة</span>
                        </h3>

                        <div v-if="recentCheckIns.length > 0" class="space-y-2">
                            <div
                                v-for="checkIn in recentCheckIns"
                                :key="checkIn.id"
                                class="flex items-center justify-between p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-xs"
                            >
                                <span class="text-slate-300 font-mono">{{
                                    checkIn.created_at
                                }}</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="
                                        checkIn.status === 'valid'
                                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                            : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
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
                            class="text-center py-6 text-xs text-slate-500"
                        >
                            لا توجد حركات تسجيل دخول مسجلة بعد
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
