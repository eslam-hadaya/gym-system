<script setup>
import { Link } from '@inertiajs/vue3';

const samplePlans = [
    {
        name: 'باقة الشهر الواحد',
        price: '150,000',
        period: '30 يوماً',
        features: ['دخول يومي كامل للصالة', 'استخدام كافة أجهزة الكارديو والحديد', 'متابعة قياسات مبدئية', 'رمز QR خاص بالدخول'],
        isPopular: false
    },
    {
        name: 'باقة 3 أشهر (الأكثر طلباً)',
        price: '400,000',
        period: '90 يوماً',
        features: ['جميع مميزات الباقة الشهرية', 'توفير 50,000 ل.س', 'جلسة توجيه تدريبي مع الكابتن', 'إمكانية تجميد الاشتراك لـ 10 أيام'],
        isPopular: true
    },
    {
        name: 'الباقة السنوية VIP',
        price: '1,400,000',
        period: '365 يوماً',
        features: ['دخول غير محدود طوال العام', 'خزانة مخصصة دائمة', 'تجميد الاشتراك لـ 30 يوماً', 'أولوية الاستشارة والمتابعة'],
        isPopular: false
    }
];
</script>

<template>
    <section id="plans" class="py-24 px-6 sm:px-12 max-w-6xl mx-auto">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-amber-400 tracking-wider uppercase">// خيارات الاشتراك</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white">باقات شفافة تناسب أهدافك</h2>
            <p class="text-sm text-slate-400">اختر الباقة المناسبة وسجّل الآن، ويمكنك تسديد الاشتراك عند الكابتن أو عبر شام كاش.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            <div
                v-for="(plan, idx) in samplePlans"
                :key="idx"
                :class="[
                    'rounded-3xl p-8 flex flex-col justify-between border transition duration-300 relative',
                    plan.isPopular
                        ? 'bg-gradient-to-b from-slate-900 via-slate-900 to-amber-950/20 border-amber-500 shadow-2xl shadow-amber-500/10'
                        : 'bg-slate-900/40 border-slate-800'
                ]"
            >
                <div v-if="plan.isPopular" class="absolute -top-3.5 right-8 bg-amber-500 text-slate-950 text-[11px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                    الخيار الأكثر شعبية
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white mb-1">{{ plan.name }}</h3>
                    <span class="text-xs text-slate-400 block mb-6">مدة الصلاحية: {{ plan.period }}</span>

                    <div class="flex items-baseline gap-1.5 mb-6">
                        <span class="text-3xl sm:text-4xl font-black text-white">{{ plan.price }}</span>
                        <span class="text-xs text-amber-400 font-bold">ليرة سورية</span>
                    </div>

                    <ul class="space-y-3 text-xs text-slate-300 mb-8 border-t border-slate-800 pt-6">
                        <li v-for="(feat, fIdx) in plan.features" :key="fIdx" class="flex items-center gap-2.5">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>{{ feat }}</span>
                        </li>
                    </ul>
                </div>

                <Link
                    href="/register"
                    :class="[
                        'w-full py-3.5 rounded-xl text-center text-sm font-bold transition block',
                        plan.isPopular
                            ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/20'
                            : 'bg-slate-800 hover:bg-slate-700 text-white'
                    ]"
                >
                    اختر هذه الباقة وسجّل
                </Link>
            </div>
        </div>
    </section>
</template>
