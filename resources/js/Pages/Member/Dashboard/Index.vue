<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const memberData = ref({
    name: 'أحمد الإبراهيم',
    member_code: 'GYM-1042',
    phone: '0988112233',
    gender: 'ذكر',
    period_label: 'فترة الرجال (02:00 م - 11:30 م)',
    plan: 'باقة 3 أشهر',
    status: 'active',
    days_left: 42,
    start_date: '2026-10-01',
    end_date: '2026-12-15',
});
</script>

<template>
    <Head title="بطاقة العضوية الرقمية" />

    <MemberLayout>
        <div class="space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-white">بطاقتي الرقمية</h1>
                    <p class="text-xs text-slate-400 mt-1">وجّه شاشة الهاتف مباشرة نحو كاميرا الباب لتسجيل الدخول السريع</p>
                </div>
                <Link
                    href="/member/dashboard/subscriptions"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs transition"
                >
                    تجديد اشتراكي
                </Link>
            </div>

            <!-- بطاقة العضوية الرقمية (Gym Pass) -->
            <div class="max-w-md mx-auto bg-gradient-to-br from-slate-900 via-slate-900 to-amber-950/30 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl"></div>

                <!-- الهيدر الداخلي للبطاقة -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-sm">🏋️</span>
                        <div>
                            <span class="text-xs font-black text-white block">نادي الأبطال</span>
                            <span class="text-[9px] text-amber-400 font-mono">DIGITAL MEMBER PASS</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold">
                        {{ memberData.status === 'active' ? 'اشتراك سارٍ' : 'منتهي' }}
                    </span>
                </div>

                <!-- مساحة رمز الـ QR عالي التباين للقارئ -->
                <div class="bg-white rounded-2xl p-6 flex flex-col items-center justify-center space-y-3 shadow-inner">
                    <!-- محاكي QR عالي الوضوح -->
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

                <!-- تفاصيل اللاعب والاشتراك -->
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
                        <span>الأيام المتبقية:</span>
                        <span class="font-mono font-bold text-white">{{ memberData.days_left }} يوم</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-400 border-t border-slate-800 pt-3">
                        <span>فترة دوام الصالة:</span>
                        <span class="text-slate-300">{{ memberData.period_label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
