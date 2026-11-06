<script setup>
import { ref, onMounted, watch } from 'vue';
import QRCode from 'qrcode';

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
        default: 'لا يوجد اشتراك نشط',
    },
    status: {
        type: String,
        default: 'pending',
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
                dark: '#020617',
                light: '#ffffff',
            },
        });
    } catch (err) {
        console.error('QR Render Error:', err);
    }
};

const downloadCard = async () => {
    isGenerating.value = true;

    try {
        const offscreenCanvas = document.createElement('canvas');
        const ctx = offscreenCanvas.getContext('2d');
        const width = 800;
        const height = 1100;

        offscreenCanvas.width = width;
        offscreenCanvas.height = height;

        // Background Gradient
        const grad = ctx.createLinearGradient(0, 0, width, height);
        grad.addColorStop(0, '#090d16');
        grad.addColorStop(1, '#020617');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, width, height);

        // Border Glow
        ctx.lineWidth = 12;
        ctx.strokeStyle = '#f59e0b';
        ctx.strokeRect(30, 30, width - 60, height - 60);

        // Header Title
        ctx.fillStyle = '#f59e0b';
        ctx.font = 'bold 44px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('نادي الأبطال الرياضي', width / 2, 120);

        ctx.fillStyle = '#94a3b8';
        ctx.font = '22px sans-serif';
        ctx.fillText('بطاقة الدخول الإلكترونية المعتمدة', width / 2, 165);

        // Member Info Box
        ctx.fillStyle = '#0f172a';
        ctx.beginPath();
        ctx.roundRect(80, 210, width - 160, 160, 24);
        ctx.fill();
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 36px sans-serif';
        ctx.fillText(props.memberName, width / 2, 275);

        ctx.fillStyle = '#f59e0b';
        ctx.font = 'bold 24px monospace';
        ctx.fillText(props.memberCode, width / 2, 325);

        // QR Code Container
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.roundRect(160, 420, 480, 480, 32);
        ctx.fill();

        // Draw High-Res QR directly
        const qrImage = new Image();
        const qrDataUrl = await QRCode.toDataURL(props.memberCode, {
            width: 420,
            margin: 1,
            color: {
                dark: '#020617',
                light: '#ffffff',
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
        ctx.fillStyle = '#0f172a';
        ctx.beginPath();
        ctx.roundRect(140, 930, width - 280, 60, 16);
        ctx.fill();

        ctx.fillStyle = '#38bdf8';
        ctx.font = 'bold 22px sans-serif';
        ctx.fillText(`الباقة: ${props.planName}`, width / 2, 968);

        // Footer Note
        ctx.fillStyle = '#64748b';
        ctx.font = '18px sans-serif';
        ctx.fillText('أبرز هذا الرمز عند البوابة الإلكترونية لتسجيل الحضور', width / 2, 1035);

        // Export as PNG
        const link = document.createElement('a');
        link.download = `GYM-PASS-${props.memberCode}.png`;
        link.href = offscreenCanvas.toDataURL('image/png');
        link.click();
    } finally {
        isGenerating.value = false;
    }
};

onMounted(() => {
    generateQr();
});

watch(() => props.memberCode, () => {
    generateQr();
});
</script>

<template>
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 flex flex-col items-center text-center shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="text-right">
                <span class="text-[10px] text-slate-400 font-semibold block">كود العضوية</span>
                <span class="font-mono text-sm font-black text-amber-400">{{ memberCode }}</span>
            </div>
            <div
                class="px-3 py-1 rounded-full text-[11px] font-bold border"
                :class="status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
            >
                {{ status === 'active' ? 'مشترك نشط' : 'بانتظار التفعيل' }}
            </div>
        </div>

        <div class="my-6 p-4 bg-white rounded-2xl shadow-xl flex items-center justify-center">
            <canvas ref="qrCanvas" class="max-w-full h-auto"></canvas>
        </div>

        <div class="space-y-1 mb-6">
            <h3 class="text-base font-bold text-white">{{ memberName }}</h3>
            <p class="text-xs text-slate-400">{{ planName }}</p>
        </div>

        <button
            type="button"
            @click="downloadCard"
            :disabled="isGenerating"
            class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl text-xs transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2 cursor-pointer"
        >
            <span v-if="isGenerating" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
            <span v-else>💾</span>
            <span>تحميل بطاقة الدخول (PNG)</span>
        </button>
        <p class="text-[10px] text-slate-500 mt-2">احفظ البطاقة على جهازك لفتحها عند الباب دون الحاجة لاتصال إنترنت</p>
    </div>
</template>
