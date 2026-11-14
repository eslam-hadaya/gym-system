<script setup>
import { ref, onMounted, watch } from "vue";
import QRCode from "qrcode";

const props = defineProps({
    memberCode: {
        type: String,
        required: true,
    },
    memberName: {
        type: String,
        required: true,
    },
    planName: {
        type: String,
        default: "لا يوجد اشتراك نشط",
    },
    status: {
        type: String,
        default: "pending",
    },
});

const qrCanvas = ref(null);
const isGenerating = ref(false);

const generateQr = async () => {
    if (!qrCanvas.value) return;

    try {
        await QRCode.toCanvas(qrCanvas.value, props.memberCode, {
            width: 220,
            margin: 1,
            color: {
                dark: "#020617",
                light: "#ffffff",
            },
        });
    } catch (err) {
        console.error("QR Render Error:", err);
    }
};

const downloadCard = async () => {
    isGenerating.value = true;

    try {
        const offscreenCanvas = document.createElement("canvas");
        const ctx = offscreenCanvas.getContext("2d");
        const width = 800;
        const height = 1100;

        offscreenCanvas.width = width;
        offscreenCanvas.height = height;

        // Background Gradient
        const grad = ctx.createLinearGradient(0, 0, width, height);
        grad.addColorStop(0, "#090d16");
        grad.addColorStop(1, "#020617");
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, width, height);

        // Border Glow
        ctx.lineWidth = 12;
        ctx.strokeStyle = "#f59e0b";
        ctx.strokeRect(30, 30, width - 60, height - 60);

        // Header Title
        ctx.fillStyle = "#f59e0b";
        ctx.font = "bold 44px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("نادي الأبطال الرياضي", width / 2, 120);

        ctx.fillStyle = "#94a3b8";
        ctx.font = "22px sans-serif";
        ctx.fillText("بطاقة الدخول الإلكترونية المعتمدة", width / 2, 165);

        // Member Info Box
        ctx.fillStyle = "#0f172a";
        ctx.beginPath();
        ctx.roundRect(80, 210, width - 160, 160, 24);
        ctx.fill();
        ctx.strokeStyle = "#1e293b";
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 36px sans-serif";
        ctx.fillText(props.memberName, width / 2, 275);

        ctx.fillStyle = "#f59e0b";
        ctx.font = "bold 24px monospace";
        ctx.fillText(props.memberCode, width / 2, 325);

        // QR Code Container
        ctx.fillStyle = "#ffffff";
        ctx.beginPath();
        ctx.roundRect(160, 420, 480, 480, 32);
        ctx.fill();

        // Draw High-Res QR directly
        const qrImage = new Image();
        const qrDataUrl = await QRCode.toDataURL(props.memberCode, {
            width: 420,
            margin: 1,
            color: {
                dark: "#020617",
                light: "#ffffff",
            },
        });

        await new Promise((resolve) => {
            qrImage.onload = () => {
                ctx.drawImage(qrImage, 190, 450, 420, 420);
                resolve();
            };
            qrImage.src = qrDataUrl;
        });

        // Plan Badge
        ctx.fillStyle = "#0f172a";
        ctx.beginPath();
        ctx.roundRect(140, 930, width - 280, 60, 16);
        ctx.fill();

        ctx.fillStyle = "#38bdf8";
        ctx.font = "bold 22px sans-serif";
        ctx.fillText(`الباقة: ${props.planName}`, width / 2, 968);

        // Footer Note
        ctx.fillStyle = "#64748b";
        ctx.font = "18px sans-serif";
        ctx.fillText(
            "أبرز هذا الرمز عند البوابة الإلكترونية لتسجيل الحضور",
            width / 2,
            1035,
        );

        // Export as PNG
        const link = document.createElement("a");
        link.download = `GYM-PASS-${props.memberCode}.png`;
        link.href = offscreenCanvas.toDataURL("image/png");
        link.click();
    } finally {
        isGenerating.value = false;
    }
};

onMounted(() => {
    generateQr();
});

watch(
    () => props.memberCode,
    () => {
        generateQr();
    },
);
</script>

<template>
    <div
        class="relative flex flex-col items-center overflow-hidden rounded-3xl border border-line-soft bg-surface-elevated p-6 text-center shadow-sm transition-colors duration-300 sm:p-8"
    >
        <!-- توهج خفيف في الخلفية -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-brand-500/10 blur-[60px]"
        ></div>

        <!-- ============ الرأس: الكود والحالة ============ -->
        <div
            class="relative z-10 flex w-full items-center justify-between border-b border-line-soft pb-4"
        >
            <div class="text-right">
                <span class="block text-[10px] font-semibold text-ink-muted"
                    >كود العضوية</span
                >
                <span
                    class="font-mono text-sm font-black text-brand-600 dark:text-brand-400"
                    dir="ltr"
                >
                    {{ memberCode }}
                </span>
            </div>
            <div
                class="rounded-full border px-3 py-1 text-[11px] font-bold"
                :class="
                    status === 'active'
                        ? 'border-success-500/30 bg-success-500/10 text-success-600 dark:text-success-400'
                        : 'border-warning-500/30 bg-warning-500/10 text-warning-600 dark:text-warning-400'
                "
            >
                {{ status === "active" ? "مشترك نشط" : "بانتظار التفعيل" }}
            </div>
        </div>

        <!-- ============ رمز الـ QR ============ -->
        <!-- ملاحظة: تم إبقاء الخلفية بيضاء هنا عشان رمز الـ QR يبقى مقروءاً دائماً -->
        <div
            class="relative z-10 my-6 flex items-center justify-center rounded-2xl bg-white p-4 shadow-md ring-1 ring-black/5"
        >
            <canvas ref="qrCanvas" class="h-auto max-w-full"></canvas>
        </div>

        <!-- ============ معلومات المشترك ============ -->
        <div class="relative z-10 mb-6 space-y-1">
            <h3 class="text-base font-bold text-ink-primary">
                {{ memberName }}
            </h3>
            <p class="text-xs text-ink-secondary">{{ planName }}</p>
        </div>

        <!-- ============ زر التحميل ============ -->
        <button
            type="button"
            @click="downloadCard"
            :disabled="isGenerating"
            class="relative z-10 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-xs font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
        >
            <template v-if="isGenerating">
                <svg
                    class="h-4 w-4 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                جاري التحميل...
            </template>
            <template v-else>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                    />
                </svg>
                تحميل بطاقة الدخول (PNG)
            </template>
        </button>

        <p
            class="relative z-10 mt-3 text-[10px] leading-relaxed text-ink-muted"
        >
            احفظ البطاقة على جهازك لفتحها عند الباب دون الحاجة لاتصال إنترنت
        </p>
    </div>
</template>
