<script setup>
import { ref } from 'vue';

const props = defineProps({
    memberCode: {
        type: String,
        required: true,
        default: 'GYM-1042',
    },
    memberName: {
        type: String,
        required: true,
        default: 'عضو مسجل',
    },
    planName: {
        type: String,
        default: 'اشتراك شهري',
    },
    expiryDate: {
        type: String,
        default: '2026-12-31',
    },
    isActive: {
        type: Boolean,
        default: true,
    },
});

const isExporting = ref(false);

const downloadQrImage = () => {
    isExporting.value = true;

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    // أبعاد البطاقة العالية الدقة
    canvas.width = 700;
    canvas.height = 950;

    // الخلفية المتدرجة الفخمة
    const gradient = ctx.createLinearGradient(0, 0, 700, 950);
    gradient.addColorStop(0, '#090d16');
    gradient.addColorStop(0.5, '#020617');
    gradient.addColorStop(1, '#1b1202');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 700, 950);

    // إطار ذهبي
    ctx.strokeStyle = '#f59e0b';
    ctx.lineWidth = 4;
    ctx.strokeRect(25, 25, 650, 900);

    // الهيدر
    ctx.fillStyle = '#f59e0b';
    ctx.font = 'bold 32px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('🏋️ نادي الأبطال الرياضي', 350, 90);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '14px monospace';
    ctx.fillText('DIGITAL ENTRY QR PASS', 350, 120);

    // صندوق الـ QR الأبيض
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(150, 160, 400, 400, 20);
    ctx.fill();

    // رسم عناصر الباركود المتباين
    ctx.fillStyle = '#000000';
    ctx.fillRect(190, 200, 80, 80);
    ctx.clearRect(205, 215, 50, 50);
    ctx.fillRect(220, 230, 20, 20);

    ctx.fillRect(430, 200, 80, 80);
    ctx.clearRect(445, 215, 50, 50);
    ctx.fillRect(460, 230, 20, 20);

    ctx.fillRect(190, 440, 80, 80);
    ctx.clearRect(205, 455, 50, 50);
    ctx.fillRect(220, 470, 20, 20);

    // بيانات وسطية
    ctx.fillRect(310, 210, 30, 30);
    ctx.fillRect(370, 250, 40, 30);
    ctx.fillRect(300, 310, 50, 40);
    ctx.fillRect(380, 330, 30, 50);
    ctx.fillRect(320, 400, 70, 30);
    ctx.fillRect(430, 420, 40, 40);
    ctx.fillRect(360, 470, 40, 30);
    ctx.fillRect(450, 500, 40, 40);

    // الكود تحت الـ QR
    ctx.fillStyle = '#020617';
    ctx.font = 'bold 24px monospace';
    ctx.fillText(props.memberCode, 350, 535);

    // بيانات العضو
    ctx.textAlign = 'right';
    ctx.fillStyle = '#94a3b8';
    ctx.font = '18px sans-serif';
    ctx.fillText('الاسم:', 580, 630);
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 22px sans-serif';
    ctx.fillText(props.memberName, 380, 630);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '18px sans-serif';
    ctx.fillText('الباقة:', 580, 680);
    ctx.fillStyle = '#f59e0b';
    ctx.font = 'bold 20px sans-serif';
    ctx.fillText(props.planName, 380, 680);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '18px sans-serif';
    ctx.fillText('الصلاحية:', 580, 730);
    ctx.fillStyle = props.isActive ? '#10b981' : '#f43f5e';
    ctx.font = 'bold 20px sans-serif';
    ctx.fillText(props.isActive ? `ساري حتى ${props.expiryDate}` : 'منتهي الصلاحية', 380, 730);

    // الفوتر
    ctx.textAlign = 'center';
    ctx.fillStyle = '#64748b';
    ctx.font = '13px sans-serif';
    ctx.fillText('امسح هذا الرمز أمام الكاميرا عند بوابة النادي للدخول الفوري', 350, 840);

    setTimeout(() => {
        const link = document.createElement('a');
        link.download = `QR-Pass-${props.memberCode}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        isExporting.value = false;
    }, 200);
};
</script>

<template>
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-amber-950/20 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden max-w-sm mx-auto text-center">
        <!-- شريط الحالة العلوي -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
            <span class="text-xs font-bold text-white flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full" :class="isActive ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500'"></span>
                <span>{{ isActive ? 'كود دخول نشط' : 'كود غير مفعل' }}</span>
            </span>
            <span class="text-[10px] font-mono text-amber-400 font-bold bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                {{ memberCode }}
            </span>
        </div>

        <!-- صندوق الـ QR الأبيض للقراءة السريعة بالضوء -->
        <div class="bg-white rounded-2xl p-6 flex flex-col items-center justify-center space-y-3 shadow-inner">
            <div class="w-44 h-44 border-4 border-slate-950 rounded-xl p-2 flex flex-col justify-between">
                <div class="flex justify-between">
                    <div class="w-9 h-9 border-4 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                    <div class="w-9 h-9 border-4 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                </div>
                <div class="text-center font-mono font-black text-slate-950 text-xs tracking-widest py-2">
                    {{ memberCode }}
                </div>
                <div class="flex justify-between">
                    <div class="w-9 h-9 border-4 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                    <div class="w-5 h-5 bg-black self-end"></div>
                </div>
            </div>
            <span class="text-[11px] font-mono font-bold text-slate-900">
                {{ memberCode }}
            </span>
        </div>

        <!-- بيانات العضو السريعة -->
        <div class="space-y-1.5 text-xs text-slate-300">
            <p class="font-bold text-white text-sm">{{ memberName }}</p>
            <p class="text-slate-400 text-[11px]">{{ planName }}</p>
        </div>

        <!-- زر التنزيل المباشر كصورة -->
        <button
            type="button"
            @click="downloadQrImage"
            :disabled="isExporting"
            class="w-full py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl text-xs transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer"
        >
            <span>{{ isExporting ? '⏳' : '📥' }}</span>
            <span>{{ isExporting ? 'جاري تجهيز الصورة...' : 'حفظ كود الدخول كصورة (PNG)' }}</span>
        </button>
    </div>
</template>
