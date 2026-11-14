<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Camera,
    VideoOff,
    CheckCircle2,
    XCircle,
    ScanLine,
    Bell,
    Clock,
    Loader2,
    User,
    CreditCard,
} from "@lucide/vue";

const props = defineProps({
    pendingRequests: {
        type: Array,
        default: () => [],
    },
    todayLogs: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_today: 0,
            valid_count: 0,
            rejected_count: 0,
            pending_count: 0,
        }),
    },
});

const page = usePage();
const codeInput = ref(null);
const flashResult = ref(null);
const processingActionId = ref(null);
let dismissTimer = null;

// حالة الكاميرا
const isCameraActive = ref(false);
const isInitializing = ref(false);
const cameraError = ref("");
let html5QrCode = null;

const form = useForm({
    member_code: "",
});

// أصوات التنبيه
let audioCtx = null;
const getAudioContext = () => {
    if (!audioCtx) {
        const AudioContextClass =
            window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) audioCtx = new AudioContextClass();
    }
    if (audioCtx && audioCtx.state === "suspended") audioCtx.resume();
    return audioCtx;
};

const playSuccessSound = () => {
    try {
        const ctx = getAudioContext();
        if (!ctx) return;
        const now = ctx.currentTime;
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = "sine";
        osc1.frequency.setValueAtTime(880, now);
        gain1.gain.setValueAtTime(0.15, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.12);

        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = "sine";
        osc2.frequency.setValueAtTime(1320, now + 0.1);
        gain2.gain.setValueAtTime(0.18, now + 0.1);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.1);
        osc2.stop(now + 0.28);
    } catch (e) {
        console.warn(e);
    }
};

const playErrorSound = () => {
    try {
        const ctx = getAudioContext();
        if (!ctx) return;
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = "sawtooth";
        osc.frequency.setValueAtTime(220, now);
        osc.frequency.setValueAtTime(160, now + 0.15);
        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.35);
    } catch (e) {
        console.warn(e);
    }
};

const focusInput = () => {
    nextTick(() => codeInput.value?.focus());
};

watch(
    () => page.props.flash?.scan_result,
    (res) => {
        if (res) {
            flashResult.value = res;
            if (res.status === "valid") {
                playSuccessSound();
            } else {
                playErrorSound();
            }

            if (dismissTimer) clearTimeout(dismissTimer);
            dismissTimer = setTimeout(() => {
                flashResult.value = null;
            }, 5000);
        }
    },
    { deep: true },
);

const submitScanCode = (code) => {
    if (!code || form.processing) return;
    form.member_code = code.trim();
    form.post("/admin/dashboard/checkin/scan", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("member_code");
            focusInput();
        },
        onError: () => {
            form.reset("member_code");
            focusInput();
        },
    });
};

const handleManualScan = () => {
    submitScanCode(form.member_code);
};

const startCamera = async () => {
    cameraError.value = "";
    isInitializing.value = true;
    isCameraActive.value = true;

    await nextTick();

    try {
        const { Html5Qrcode } = await import("html5-qrcode");

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader-box");
        }

        await html5QrCode.start(
            { facingMode: "user" },
            {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const qrboxSize = Math.floor(minEdge * 0.7);
                    return {
                        width: qrboxSize,
                        height: qrboxSize,
                    };
                },
            },
            (decodedText) => {
                submitScanCode(decodedText);
            },
            (errorMessage) => {
                // تجاهل أخطاء الفريمات
            },
        );
    } catch (err) {
        console.error("Camera Init Error:", err);
        cameraError.value =
            "تعذر تشغيل الكاميرا؛ تأكد من منح الإذن للمتصفح وإغلاق أي تطبيق آخر يستخدمها.";
        isCameraActive.value = false;
    } finally {
        isInitializing.value = false;
    }
};

const stopCamera = async () => {
    if (html5QrCode && isCameraActive.value) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (e) {
            console.warn(e);
        }
        isCameraActive.value = false;
    }
};

const approveCheckIn = (id) => {
    processingActionId.value = id;
    router.post(
        `/admin/dashboard/checkin/requests/${id}/approve`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingActionId.value = null;
            },
        },
    );
};

const rejectCheckIn = (id) => {
    if (confirm("هل أنت متأكد من رفض طلب الدخول لهذا اللاعب؟")) {
        processingActionId.value = id;
        router.post(
            `/admin/dashboard/checkin/requests/${id}/reject`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    processingActionId.value = null;
                },
            },
        );
    }
};

onMounted(() => {
    focusInput();
});

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <Head title="كشك الاستقبال ومسح الدخول" />

    <AdminLayout>
        <div class="space-y-6" dir="rtl">
            <!-- ============ الترويسة والإحصائيات ============ -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-xl font-black tracking-tight text-ink-primary sm:text-2xl"
                    >
                        كشك الاستقبال وإدارة الدخول
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                        المسح التلقائي بالـ QR عبر الكاميرا الحية أو قارئ
                        الباركود
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div
                        class="flex items-center gap-2 rounded-xl border border-warning-500/30 bg-warning-500/10 px-3 py-1.5 sm:px-4 sm:py-2"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-warning-500 animate-pulse"
                        ></span>
                        <span
                            class="text-[11px] font-bold text-warning-600 dark:text-warning-400 sm:text-xs"
                        >
                            طلبات معلقة: {{ stats.pending_count || 0 }}
                        </span>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-success-500/30 bg-success-500/10 px-3 py-1.5 sm:px-4 sm:py-2"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-success-500"
                        ></span>
                        <span
                            class="text-[11px] font-bold text-success-600 dark:text-success-400 sm:text-xs"
                        >
                            حضور اليوم: {{ stats.valid_count || 0 }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ============ طلبات الدخول المعلقة ============ -->
            <div
                v-if="pendingRequests.length > 0"
                class="space-y-4 rounded-3xl border border-warning-500/30 bg-warning-500/5 p-4 sm:p-6"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Bell
                            class="h-5 w-5 text-warning-500"
                            :stroke-width="2"
                        />
                        <h2
                            class="text-sm font-black text-warning-600 dark:text-warning-400 sm:text-base"
                        >
                            طلبات الدخول بانتظار الموافقة ({{
                                pendingRequests.length
                            }})
                        </h2>
                    </div>
                    <span
                        class="text-[10px] font-bold text-warning-500/80 sm:text-xs"
                    >
                        لاعبون بالاستقبال
                    </span>
                </div>

                <div
                    class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="req in pendingRequests"
                        :key="req.id"
                        class="flex items-center justify-between rounded-2xl border border-line-soft bg-surface-elevated p-4 shadow-sm"
                    >
                        <div>
                            <div class="text-sm font-black text-ink-primary">
                                {{ req.name }}
                            </div>
                            <div class="mt-0.5 text-[11px] text-ink-secondary">
                                <span
                                    class="font-mono font-bold text-brand-600 dark:text-brand-400"
                                    >{{ req.code }}</span
                                >
                                • {{ req.plan }}
                            </div>
                            <span
                                class="mt-1 block font-mono text-[10px] text-ink-muted"
                            >
                                {{ req.time }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                @click="approveCheckIn(req.id)"
                                :disabled="processingActionId === req.id"
                                class="inline-flex items-center gap-1 rounded-xl bg-success-500 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-sm transition hover:bg-success-600 disabled:opacity-50 sm:px-3 sm:text-xs"
                            >
                                <Loader2
                                    v-if="processingActionId === req.id"
                                    class="h-3 w-3 animate-spin"
                                    :stroke-width="2.5"
                                />
                                <CheckCircle2
                                    v-else
                                    class="h-3.5 w-3.5"
                                    :stroke-width="2.5"
                                />
                                دخول
                            </button>
                            <button
                                @click="rejectCheckIn(req.id)"
                                :disabled="processingActionId === req.id"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-danger-500/30 bg-danger-500/10 text-danger-500 transition hover:bg-danger-500/20 disabled:opacity-50"
                            >
                                <XCircle
                                    class="h-3.5 w-3.5"
                                    :stroke-width="2.5"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ منطقة المسح الضوئي ============ -->
            <div
                class="relative overflow-hidden rounded-3xl border border-line-soft bg-surface-elevated p-5 shadow-sm sm:p-6 md:p-8"
            >
                <div class="mx-auto max-w-xl space-y-5 text-center">
                    <!-- أزرار الكاميرا -->
                    <div class="flex items-center justify-center gap-3">
                        <button
                            v-if="!isCameraActive"
                            type="button"
                            @click="startCamera"
                            :disabled="isInitializing"
                            class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-xs font-black text-white shadow-glow transition hover:bg-brand-400 disabled:opacity-50 sm:px-6 sm:text-sm"
                        >
                            <Loader2
                                v-if="isInitializing"
                                class="h-4 w-4 animate-spin"
                                :stroke-width="2.5"
                            />
                            <Camera v-else class="h-4 w-4" :stroke-width="2" />
                            <span>
                                {{
                                    isInitializing
                                        ? "جارٍ تشغيل الكاميرا..."
                                        : "تشغيل كاميرا الاستقبال للمسح"
                                }}
                            </span>
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="stopCamera"
                            class="inline-flex items-center gap-2 rounded-2xl bg-danger-500 px-5 py-3 text-xs font-black text-white shadow-glow transition hover:bg-danger-600 sm:px-6 sm:text-sm"
                        >
                            <VideoOff class="h-4 w-4" :stroke-width="2" />
                            <span>إيقاف تشغيل الكاميرا</span>
                        </button>
                    </div>

                    <!-- رسالة خطأ الكاميرا -->
                    <div
                        v-if="cameraError"
                        class="flex items-center justify-center gap-2 rounded-2xl border border-danger-500/30 bg-danger-500/10 p-3 text-[11px] font-bold text-danger-500 sm:text-xs"
                    >
                        <XCircle class="h-4 w-4" :stroke-width="2" />
                        {{ cameraError }}
                    </div>

                    <!-- شاشة الكاميرا المباشرة -->
                    <div
                        v-if="isCameraActive"
                        class="relative mx-auto aspect-square w-full max-w-[300px] overflow-hidden rounded-3xl border-2 border-brand-500/50 bg-black shadow-xl sm:max-w-[340px]"
                    >
                        <div id="qr-reader-box" class="h-full w-full"></div>

                        <div
                            class="pointer-events-none absolute inset-0 z-10 flex flex-col items-center justify-center p-6"
                        >
                            <div
                                class="relative flex h-48 w-48 items-center justify-center rounded-2xl border border-brand-500/30 sm:h-52 sm:w-52"
                            >
                                <!-- زوايا الإطار -->
                                <span
                                    class="absolute -top-1 -right-1 h-6 w-6 rounded-tr-xl border-t-4 border-r-4 border-brand-500"
                                ></span>
                                <span
                                    class="absolute -top-1 -left-1 h-6 w-6 rounded-tl-xl border-t-4 border-l-4 border-brand-500"
                                ></span>
                                <span
                                    class="absolute -bottom-1 -right-1 h-6 w-6 rounded-br-xl border-b-4 border-r-4 border-brand-500"
                                ></span>
                                <span
                                    class="absolute -bottom-1 -left-1 h-6 w-6 rounded-bl-xl border-b-4 border-l-4 border-brand-500"
                                ></span>

                                <!-- خط المسح الليزري -->
                                <div
                                    class="absolute inset-x-2 h-0.5 bg-brand-500 shadow-[0_0_8px_rgba(var(--color-brand-500),0.8)] animate-bounce"
                                ></div>
                            </div>

                            <span
                                class="mt-3 select-none rounded-full bg-black/60 px-3 py-1 text-[10px] font-bold text-brand-400 backdrop-blur-sm sm:text-[11px]"
                            >
                                ضع كود الـ QR داخل الإطار
                            </span>
                        </div>
                    </div>

                    <!-- الإدخال اليدوي -->
                    <div class="border-t border-line-soft pt-4">
                        <p
                            class="mb-2 text-[11px] text-ink-secondary sm:text-xs"
                        >
                            أو المسح بقارئ الباركود USB / إدخال الكود يدوياً
                        </p>
                        <form
                            @submit.prevent="handleManualScan"
                            class="relative mx-auto max-w-sm"
                        >
                            <input
                                ref="codeInput"
                                v-model="form.member_code"
                                type="text"
                                placeholder="GYM-1001"
                                autocomplete="off"
                                :disabled="form.processing"
                                class="w-full rounded-2xl border-2 border-line-strong bg-surface-base px-5 py-3.5 pr-14 text-center font-mono text-lg font-black tracking-widest text-brand-600 outline-none transition-all placeholder:text-ink-muted focus:border-brand-500 focus:ring-2 focus:ring-brand-300 dark:text-brand-400"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="absolute bottom-2 left-2 top-2 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3 text-xs font-bold text-white transition hover:bg-brand-400 disabled:opacity-50 sm:px-4"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="h-3.5 w-3.5 animate-spin"
                                    :stroke-width="2.5"
                                />
                                <ScanLine
                                    v-else
                                    class="h-4 w-4"
                                    :stroke-width="2"
                                />
                                فحص
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ============ بطاقة النتيجة ============ -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
            >
                <div
                    v-if="flashResult"
                    class="rounded-3xl border-2 p-5 shadow-lg sm:p-6"
                    :class="
                        flashResult.status === 'valid'
                            ? 'border-success-500 bg-success-500 text-white'
                            : 'border-danger-500 bg-danger-500 text-white'
                    "
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20"
                            >
                                <CheckCircle2
                                    v-if="flashResult.status === 'valid'"
                                    class="h-6 w-6"
                                    :stroke-width="2.5"
                                />
                                <XCircle
                                    v-else
                                    class="h-6 w-6"
                                    :stroke-width="2.5"
                                />
                            </div>
                            <div>
                                <span
                                    class="block font-mono text-[11px] font-bold opacity-80"
                                    >{{ flashResult.code }}</span
                                >
                                <h3 class="text-lg font-black sm:text-xl">
                                    {{ flashResult.name }}
                                </h3>
                                <p
                                    class="text-[11px] font-medium opacity-90 sm:text-xs"
                                >
                                    {{ flashResult.note }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="shrink-0 rounded-xl bg-black/20 px-4 py-2 text-center text-xs font-black sm:text-left"
                        >
                            {{ flashResult.plan }}
                        </div>
                    </div>
                </div>
            </transition>

            <!-- ============ سجل حضور اليوم ============ -->
            <div
                class="overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated"
            >
                <div
                    class="flex items-center justify-between border-b border-line-soft p-4"
                >
                    <h3 class="text-sm font-black text-ink-primary">
                        سجل حركات اليوم المباشر
                    </h3>
                    <span
                        class="text-[10px] font-bold text-ink-muted sm:text-xs"
                        >آخر 20 حركة</span
                    >
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead
                            class="border-b border-line-soft bg-surface-soft text-[11px] font-bold text-ink-secondary sm:text-xs"
                        >
                            <tr>
                                <th class="p-3 sm:p-4">كود العضوية</th>
                                <th class="p-3 sm:p-4">اسم اللاعب</th>
                                <th class="p-3 sm:p-4">الباقة</th>
                                <th class="p-3 sm:p-4">التوقيت</th>
                                <th class="p-3 sm:p-4">الحالة</th>
                                <th class="p-3 sm:p-4">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-line-soft font-medium text-ink-primary"
                        >
                            <tr
                                v-for="log in todayLogs"
                                :key="log.id"
                                class="transition-colors hover:bg-glass-bg-hover"
                            >
                                <td
                                    class="p-3 font-mono text-xs font-bold text-ink-muted sm:p-4"
                                >
                                    {{ log.code }}
                                </td>
                                <td class="p-3 font-black sm:p-4">
                                    {{ log.name }}
                                </td>
                                <td class="p-3 text-xs font-bold sm:p-4">
                                    {{ log.plan }}
                                </td>
                                <td
                                    class="p-3 font-mono text-[11px] text-ink-secondary sm:p-4 sm:text-xs"
                                >
                                    {{ log.time }}
                                </td>
                                <td class="p-3 sm:p-4">
                                    <span
                                        v-if="log.status === 'valid'"
                                        class="inline-block whitespace-nowrap rounded-lg border border-success-500/30 bg-success-500/10 px-2.5 py-1 text-[10px] font-black text-success-600 dark:text-success-400 sm:text-xs"
                                    >
                                        دخول مصرح
                                    </span>
                                    <span
                                        v-else
                                        class="inline-block whitespace-nowrap rounded-lg border border-danger-500/30 bg-danger-500/10 px-2.5 py-1 text-[10px] font-black text-danger-500 sm:text-xs"
                                    >
                                        مرفوض / منتهٍ
                                    </span>
                                </td>
                                <td
                                    class="p-3 text-[11px] text-ink-muted sm:p-4 sm:text-xs"
                                >
                                    {{ log.note }}
                                </td>
                            </tr>
                            <tr v-if="todayLogs.length === 0">
                                <td
                                    colspan="6"
                                    class="p-6 text-center text-xs text-ink-muted"
                                >
                                    لا توجد حركات حضور مسجلة بعد اليوم.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
/* فرض ظهور فيديو الكاميرا بالحجم الكامل داخل الصندوق */
#qr-reader-box {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
}

#qr-reader-box video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

#qr-reader-box__scan_region {
    width: 100% !important;
    height: 100% !important;
}

#qr-reader-box__dashboard_section {
    display: none !important;
}
</style>
