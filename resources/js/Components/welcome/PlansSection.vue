<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const getActionLink = (planId) => {
    if (!user.value) {
        return `/register?plan_id=${planId}`;
    }
    return user.value.role === 'admin'
        ? '/admin/dashboard/plans'
        : `/member/dashboard/subscriptions?plan_id=${planId}`;
};

const getActionText = computed(() => {
    if (!user.value) return 'اختر هذه الباقة وسجّل';
    return user.value.role === 'admin' ? 'تعديل الباقة من الإدارة' : 'اشترك بهذه الباقة الآن';
});
</script>

<template>
    <section id="plans" class="py-24 px-6 sm:px-12 max-w-6xl mx-auto select-none" dir="rtl">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-amber-400 tracking-wider uppercase font-mono">// خيارات الاشتراك المتاحة</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white">باقات شفافة تناسب أهدافك</h2>
            <p class="text-sm text-slate-400">اختر الباقة المناسبة وسجّل الآن، ويمكنك تسديد الاشتراك عند الكابتن أو عبر شام كاش.</p>
        </div>

        <div v-if="plans.length === 0" class="p-8 text-center bg-slate-900/40 border border-slate-800 rounded-3xl text-slate-400 text-sm">
            لا توجد باقات متاحة حالياً في النظام.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            <div
                v-for="plan in plans"
                :key="plan.id"
                :class="[
                    'rounded-3xl p-8 flex flex-col justify-between border transition duration-300 relative',
                    plan.days === 90
                        ? 'bg-gradient-to-b from-slate-900 via-slate-900 to-amber-950/20 border-amber-500 shadow-2xl shadow-amber-500/10'
                        : 'bg-slate-900/40 border-slate-800'
                ]"
            >
                <div v-if="plan.days === 90" class="absolute -top-3.5 right-8 bg-amber-500 text-slate-950 text-[11px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                    الخيار الأكثر شعبية
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white mb-1">{{ plan.name }}</h3>
                    <span class="text-xs text-slate-400 block mb-6">مدة الصلاحية: {{ plan.days }} يوماً</span>

                    <div class="flex items-baseline gap-1.5 mb-6">
                        <span class="text-3xl sm:text-4xl font-black text-white font-mono">{{ Number(plan.price).toLocaleString() }}</span>
                        <span class="text-xs text-amber-400 font-bold">ليرة سورية</span>
                    </div>

                    <ul class="space-y-3 text-xs text-slate-300 mb-8 border-t border-slate-800 pt-6">
                        <li class="flex items-center gap-2.5">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>دخول كامل للصالة ومعدات التدريب</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>بطاقة رقمية ورمز QR ذكي على الهاتف</span>
                        </li>
                        <li v-if="plan.days >= 90" class="flex items-center gap-2.5">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>إمكانية تجميد مؤقت وجلسة متابعة قياسات</span>
                        </li>
                    </ul>
                </div>

                <Link
                    :href="getActionLink(plan.id)"
                    :class="[
                        'w-full py-3.5 rounded-xl text-center text-sm font-bold transition block cursor-pointer',
                        plan.days === 90
                            ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/20'
                            : 'bg-slate-800 hover:bg-slate-700 text-white'
                    ]"
                >
                    {{ getActionText }}
                </Link>
            </div>
        </div>
    </section>
</template>
