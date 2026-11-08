<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

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

// إرسال الكود
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

// تشغيل الكاميرا الحية مع دعم جميع أنواع الكاميرات المتاحة
// تشغيل كاميرا اللابتوب الأمامية المدمجة
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

        // إعدادات مخصصة لكاميرا اللابتوب الأمامية
        await html5QrCode.start(
            { facingMode: "user" }, // طلب الكاميرا الأمامية للابتوب حصراً
            {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    // حساب الحجم تلقائياً ليتطابق مع شاشة اللابتوب
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
                // تجاهل أخطاء الفريمات العادية أثناء انتظار توجيه الكود
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
// إيقاف الكاميرا
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
        <div class="space-y-6">
            <!-- الترويسة والإحصائيات اللحظية -->
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-black text-slate-900 tracking-tight"
                    >
                        كشك الاستقبال وإدارة الدخول
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        المسح التلقائي بالـ QR عبر الكاميرا الحية أو قارئ
                        الباركود
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-2"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"
                        ></span>
                        <span class="text-xs font-bold text-amber-800"
                            >طلبات معلقة: {{ stats.pending_count || 0 }}</span
                        >
                    </div>
                    <div
                        class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-emerald-500"
                        ></span>
                        <span class="text-xs font-bold text-emerald-800"
                            >حضور اليوم: {{ stats.valid_count || 0 }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- طلبات الدخول المعلقة -->
            <div
                v-if="pendingRequests.length > 0"
                class="bg-amber-500/10 border-2 border-amber-500/30 rounded-3xl p-6 space-y-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🛎️</span>
                        <h2 class="text-base font-black text-amber-950">
                            طلبات الدخول بانتظار الموافقة ({{
                                pendingRequests.length
                            }})
                        </h2>
                    </div>
                    <span class="text-xs font-bold text-amber-800 animate-pulse"
                        >لاعبون بالاستقبال</span
                    >
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                >
                    <div
                        v-for="req in pendingRequests"
                        :key="req.id"
                        class="bg-white p-4 rounded-2xl border border-amber-200 shadow-xs flex items-center justify-between"
                    >
                        <div>
                            <div class="font-black text-slate-900 text-sm">
                                {{ req.name }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                <span
                                    class="font-mono text-emerald-600 font-bold"
                                    >{{ req.code }}</span
                                >
                                • {{ req.plan }}
                            </div>
                            <span
                                class="text-[11px] text-slate-400 font-mono block mt-1"
                                >{{ req.time }}</span
                            >
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                @click="approveCheckIn(req.id)"
                                :disabled="processingActionId === req.id"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer disabled:opacity-50"
                            >
                                دخول ✓
                            </button>
                            <button
                                @click="rejectCheckIn(req.id)"
                                :disabled="processingActionId === req.id"
                                class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold cursor-pointer disabled:opacity-50"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- منطقة المسح الضوئي وعرض الكاميرا -->
            <div
                class="bg-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-800 relative overflow-hidden"
            >
                <div class="max-w-xl mx-auto text-center space-y-6">
                    <!-- زر تشغيل/إيقاف الكاميرا -->
                    <div class="flex items-center justify-center gap-3">
                        <button
                            v-if="!isCameraActive"
                            type="button"
                            @click="startCamera"
                            :disabled="isInitializing"
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl font-black text-xs transition-all flex items-center gap-2 cursor-pointer shadow-lg shadow-emerald-950/60 disabled:opacity-50"
                        >
                            <span
                                v-if="isInitializing"
                                class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
                            ></span>
                            <span v-else>📷</span>
                            <span>{{
                                isInitializing
                                    ? "جارٍ تشغيل الكاميرا..."
                                    : "تشغيل كاميرا الاستقبال للمسح"
                            }}</span>
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="stopCamera"
                            class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black text-xs transition-all flex items-center gap-2 cursor-pointer shadow-lg shadow-rose-950/60"
                        >
                            <span>⏹️</span>
                            <span>إيقاف تشغيل الكاميرا</span>
                        </button>
                    </div>

                    <!-- رسائل الخطأ -->
                    <div
                        v-if="cameraError"
                        class="p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold rounded-2xl"
                    >
                        {{ cameraError }}
                    </div>

                    <!-- شاشة الكاميرا المباشرة -->
                    <div
                        v-if="isCameraActive"
                        class="relative w-full max-w-[340px] aspect-square mx-auto rounded-3xl overflow-hidden border-2 border-emerald-500/50 bg-black shadow-2xl"
                    >
                        <!-- الحاوية التي تحقن المكتبة الفيديو بداخلها -->
                        <div id="qr-reader-box" class="w-full h-full"></div>

                        <!-- إطار التوجيه فوق الفيديو مباشرة دون حجب الرؤية -->
                        <div
                            class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-6 z-10"
                        >
                            <div
                                class="relative w-52 h-52 border border-emerald-500/30 rounded-2xl flex items-center justify-center"
                            >
                                <!-- زوايا النيون -->
                                <span
                                    class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"
                                ></span>
                                <span
                                    class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"
                                ></span>
                                <span
                                    class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"
                                ></span>
                                <span
                                    class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"
                                ></span>

                                <!-- خط المسح الليزري -->
                                <div
                                    class="absolute inset-x-2 h-0.5 bg-gradient-to-r from-transparent via-rose-500 to-transparent shadow-[0_0_8px_#f43f5e] animate-bounce"
                                ></div>
                            </div>

                            <span
                                class="mt-3 text-[11px] font-bold text-emerald-400/90 bg-slate-950/80 px-3 py-1 rounded-full backdrop-blur-xs select-none"
                            >
                                ضع كود الـ QR داخل الإطار
                            </span>
                        </div>
                    </div>

                    <!-- الإدخال اليدوي / قارئ الباركود USB -->
                    <div class="pt-4 border-t border-slate-800/80">
                        <p class="text-xs text-slate-400 mb-2">
                            أو المسح بقارئ الباركود USB / إدخال الكود يدوياً
                        </p>
                        <form
                            @submit.prevent="handleManualScan"
                            class="relative max-w-sm mx-auto"
                        >
                            <input
                                ref="codeInput"
                                v-model="form.member_code"
                                type="text"
                                placeholder="GYM-1001"
                                autocomplete="off"
                                :disabled="form.processing"
                                class="w-full px-5 py-3.5 bg-slate-950 border-2 border-slate-800 focus:border-emerald-500 rounded-2xl text-center text-lg font-mono font-black tracking-widest text-emerald-400 outline-none transition-all placeholder:text-slate-700"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="absolute left-2 top-2 bottom-2 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs cursor-pointer transition-all"
                            >
                                فحص
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- بطاقة النتيجة Flash Result -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
            >
                <div
                    v-if="flashResult"
                    :class="[
                        'p-6 rounded-3xl border-2 shadow-2xl transition-all',
                        flashResult.status === 'valid'
                            ? 'bg-emerald-600 border-emerald-400 text-white'
                            : 'bg-rose-600 border-rose-400 text-white',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl font-black bg-white/20"
                            >
                                <span v-if="flashResult.status === 'valid'"
                                    >✓</span
                                >
                                <span v-else>✕</span>
                            </div>
                            <div>
                                <span
                                    class="text-xs font-mono font-bold opacity-80"
                                    >{{ flashResult.code }}</span
                                >
                                <h3 class="text-xl font-black">
                                    {{ flashResult.name }}
                                </h3>
                                <p class="text-xs opacity-90 font-medium">
                                    {{ flashResult.note }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="text-left font-black text-sm bg-black/20 px-4 py-2 rounded-xl"
                        >
                            {{ flashResult.plan }}
                        </div>
                    </div>
                </div>
            </transition>

            <!-- سجل حضور اليوم -->
            <div
                class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs"
            >
                <div
                    class="p-4 border-b border-slate-100 flex items-center justify-between"
                >
                    <h3 class="font-black text-slate-900 text-sm">
                        سجل حركات اليوم المباشر
                    </h3>
                    <span class="text-xs text-slate-400 font-bold"
                        >آخر 20 حركة</span
                    >
                </div>

                <table class="w-full text-right text-sm">
                    <thead
                        class="bg-slate-50 text-slate-500 text-xs font-bold border-b border-slate-200"
                    >
                        <tr>
                            <th class="p-4">كود العضوية</th>
                            <th class="p-4">اسم اللاعب</th>
                            <th class="p-4">الباقة</th>
                            <th class="p-4">التوقيت</th>
                            <th class="p-4">الحالة</th>
                            <th class="p-4">ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 font-medium text-slate-700"
                    >
                        <tr v-for="log in todayLogs" :key="log.id">
                            <td
                                class="p-4 font-mono font-bold text-xs text-slate-400"
                            >
                                {{ log.code }}
                            </td>
                            <td class="p-4 font-black text-slate-900">
                                {{ log.name }}
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-700">
                                {{ log.plan }}
                            </td>
                            <td class="p-4 font-mono text-xs text-slate-500">
                                {{ log.time }}
                            </td>
                            <td class="p-4">
                                <span
                                    v-if="log.status === 'valid'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200"
                                >
                                    دخول مصرح
                                </span>
                                <span
                                    v-else
                                    class="px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 text-rose-700 border border-rose-200"
                                >
                                    مرفوض / منتهٍ
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-400">
                                {{ log.note }}
                            </td>
                        </tr>
                        <tr v-if="todayLogs.length === 0">
                            <td
                                colspan="6"
                                class="p-6 text-center text-slate-400 text-xs"
                            >
                                لا توجد حركات حضور مسجلة بعد اليوم.
                            </td>
                        </tr>
                    </tbody>
                </table>
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
