<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const memberData = ref({
    name: 'أحمد الإبراهيم',
    member_code: 'GYM-1042',
    phone: '0988112233',
    gender: 'ذكر',
    period_label: 'فترة الرجال (مسائي)',
    plan: 'باقة 3 أشهر',
    status: 'active',
    days_left: 42,
    end_date: '2026-12-15',
});

const isDownloading = ref(false);

// توليد بطاقة العضوية الرسمية وتنزيلها بصيغة PNG
const downloadPassCard = () => {
    isDownloading.value = true;

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    // أبعاد البطاقة بدقة عالية (Retina Quality)
    canvas.width = 800;
    canvas.height = 1100;

    // 1. الخلفية الداكنة المتدرجة
    const bgGradient = ctx.createLinearGradient(0, 0, 800, 1100);
    bgGradient.addColorStop(0, '#090d16');
    bgGradient.addColorStop(0.5, '#020617');
    bgGradient.addColorStop(1, '#1e1302');
    ctx.fillStyle = bgGradient;
    ctx.fillRect(0, 0, 800, 1100);

    // 2. إطار خارجي مذهب فخم
    ctx.strokeStyle = '#f59e0b';
    ctx.lineWidth = 4;
    ctx.strokeRect(30, 30, 740, 1040);

    // 3. الترويسة والشعار
    ctx.fillStyle = '#f59e0b';
    ctx.font = 'bold 38px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('🏋️ نادي الأبطال الرياضي', 400, 110);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '16px monospace';
    ctx.fillText('OFFICIAL DIGITAL ATHLETE PASS', 400, 145);

    // خط فاصل علوي
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(80, 175);
    ctx.lineTo(720, 175);
    ctx.stroke();

    // 4. صندوق الـ QR الأبيض العاكس للضوء
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(175, 220, 450, 450, 24);
    ctx.fill();

    // رسم محاكي الـ QR داخل الصندوق الأبيض
    ctx.fillStyle = '#000000';
    // الزاوية العلوية يمين
    ctx.fillRect(215, 260, 90, 90);
    ctx.clearRect(230, 275, 60, 60);
    ctx.fillRect(245, 290, 30, 30);
    // الزاوية العلوية يسار
    ctx.fillRect(495, 260, 90, 90);
    ctx.clearRect(510, 275, 60, 60);
    ctx.fillRect(525, 290, 30, 30);
    // الزاوية السفلية يمين
    ctx.fillRect(215, 540, 90, 90);
    ctx.clearRect(230, 555, 60, 60);
    ctx.fillRect(245, 570, 30, 30);

    // شبكة نقاط ومربعات الـ Data الوسطية
    ctx.fillRect(350, 270, 30, 30);
    ctx.fillRect(420, 310, 40, 30);
    ctx.fillRect(330, 360, 50, 40);
    ctx.fillRect(430, 380, 30, 50);
    ctx.fillRect(360, 450, 80, 30);
    ctx.fillRect(490, 480, 40, 40);
    ctx.fillRect(400, 530, 50, 30);
    ctx.fillRect(330, 580, 40, 40);
    ctx.fillRect(520, 560, 50, 50);

    // كود العضوية تحت الـ QR مباشرة
    ctx.fillStyle = '#020617';
    ctx.font = 'bold 26px monospace';
    ctx.fillText(memberData.value.member_code, 400, 645);

    // 5. بيانات العضو والاشتراك
    ctx.textAlign = 'right';

    ctx.fillStyle = '#94a3b8';
    ctx.font = '20px sans-serif';
    ctx.fillText('اسم اللاعب:', 680, 750);
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 24px sans-serif';
    ctx.fillText(memberData.value.name, 450, 750);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '20px sans-serif';
    ctx.fillText('نوع الباقة:', 680, 810);
    ctx.fillStyle = '#f59e0b';
    ctx.font = 'bold 22px sans-serif';
    ctx.fillText(memberData.value.plan, 450, 810);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '20px sans-serif';
    ctx.fillText('صلاحية الدخول:', 680, 870);
    ctx.fillStyle = '#10b981';
    ctx.font = 'bold 22px sans-serif';
    ctx.fillText(`ساري حتى ${memberData.value.end_date}`, 450, 870);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '20px sans-serif';
    ctx.fillText('فترة التدريب:', 680, 930);
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '20px sans-serif';
    ctx.fillText(memberData.value.period_label, 450, 930);

    // 6. الفوتر والتحذير الأمني
    ctx.textAlign = 'center';
    ctx.fillStyle = '#64748b';
    ctx.font = '15px sans-serif';
    ctx.fillText('هذه البطاقة مخصصة للاستخدام الشخصي وتُمسح على بوابة الدخول الإلكترونية', 400, 1010);

    // تصدير الصورة والتنزيل التلقائي
    setTimeout(() => {
        const link = document.createElement('a');
        link.download = `Gym-Pass-${memberData.value.member_code}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        isDownloading.value = false;
    }, 250);
};
</script>

<template>
    <Head title="بطاقة العضوية الرقمية" />

    <MemberLayout>
        <div class="space-y-8">
            <!-- الترويسة والأزرار العلوية -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white">بطاقتي الرقمية للباب</h1>
                    <p class="text-xs text-slate-400 mt-1">امسح الكود مباشرة على الباب أو حمّل البطاقة كصورة على جهازك</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- زر تنزيل البطاقة الاحترافية كصورة -->
                    <button
                        type="button"
                        @click="downloadPassCard"
                        :disabled="isDownloading"
                        class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition border border-slate-700 shadow-md cursor-pointer"
                    >
                        <span>{{ isDownloading ? '⏳' : '📥' }}</span>
                        <span>{{ isDownloading ? 'جاري تجهيز الصورة...' : 'تنزيل البطاقة كصورة (PNG)' }}</span>
                    </button>

                    <Link
                        href="/member/dashboard/subscriptions"
                        class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs transition shadow-lg shadow-amber-500/20"
                    >
                        تجديد اشتراكي
                    </Link>
                </div>
            </div>

            <!-- بطاقة العضوية الرقمية في الواجهة -->
            <div class="max-w-md mx-auto bg-gradient-to-br from-slate-900 via-slate-900 to-amber-950/30 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl"></div>

                <!-- ترويسة البطاقة -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-sm shadow-md shadow-amber-500/20">🏋️</span>
                        <div>
                            <span class="text-xs font-black text-white block">نادي الأبطال</span>
                            <span class="text-[9px] text-amber-400 font-mono tracking-wider">OFFICIAL ATHLETE PASS</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold">
                        {{ memberData.status === 'active' ? 'اشتراك سارٍ' : 'منتهي' }}
                    </span>
                </div>

                <!-- مربع الـ QR -->
                <div class="bg-white rounded-2xl p-6 flex flex-col items-center justify-center space-y-3 shadow-inner">
                    <div class="w-48 h-48 border-4 border-slate-950 rounded-xl p-2 flex flex-col justify-between">
                        <div class="flex justify-between">
                            <div class="w-10 h-10 border-4 border-black p-1"><div class="w-full h-full bg-black"></div></div>
                            <div class="w-10 h-10 border-4 border-black p-1"><div class="w-full h-full bg-black"></div></div>
                        </div>
                        <div class="text-center font-mono font-black text-slate-950 text-sm tracking-widest py-3">
                            {{ memberData.member_code }}
                        </div>
                        <div class="flex justify-between">
                            <div class="w-10 h-10 border-4 border-black p-1"><div class="w-full h-full bg-black"></div></div>
                            <div class="w-6 h-6 bg-black self-end"></div>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-slate-900 tracking-wider">
                        {{ memberData.member_code }}
                    </span>
                </div>

                <!-- تفاصيل اللاعب -->
                <div class="space-y-3 text-xs pt-2">
                    <div class="flex justify-between items-center text-slate-400">
                        <span>اسم اللاعب:</span>
                        <strong class="text-white">{{ memberData.name }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-400">
                        <span>الباقة الحالية:</span>
                        <span class="text-amber-400 font-semibold">{{ memberData.plan }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-400">
                        <span>المتبقي:</span>
                        <span class="font-mono font-bold text-emerald-400">{{ memberData.days_left }} يوماً</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-400 border-t border-slate-800 pt-3">
                        <span>فترة الدوام:</span>
                        <span class="text-slate-300">{{ memberData.period_label }}</span>
                    </div>
                </div>

                <!-- زر التنزيل السريع داخل البطاقة للهواتف -->
                <button
                    type="button"
                    @click="downloadPassCard"
                    :disabled="isDownloading"
                    class="w-full py-3 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>📥 حفظ البطاقة كصورة على الموبايل</span>
                </button>
            </div>
        </div>
    </MemberLayout>
</template>
