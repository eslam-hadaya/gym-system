<script setup>
import MemberLayout from '@/Layouts/MemberLayout.vue';
import QrDisplayCard from '@/Pages/Member/Dashboard/Index.vue';

defineProps({
    member: {
        type: Object,
        required: true,
    },
    subscription: {
        type: Object,
        default: null,
    },
    recentCheckIns: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <MemberLayout>
        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-amber-500/15 via-slate-900 to-slate-900 p-6 rounded-3xl border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-white">أهلاً بك يا بطل، {{ member.name }} 👋</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ member.gender === 'male' ? 'فترة التدريب: مسائية (رجال)' : 'فترة التدريب: صباحية (سيدات)' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">حالة الحساب:</span>
                    <span class="text-xs font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20">
                        {{ member.status === 'active' ? 'نشط ومصرح بالدخول' : 'بانتظار الاشتراك' }}
                    </span>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <!-- QR Pass Card (Left / Top on mobile) -->
                <div class="md:col-span-5">
                    <QrDisplayCard
                        :member-code="member.member_code"
                        :member-name="member.name"
                        :plan-name="subscription?.plan_name || 'لا يوجد اشتراك مفعل'"
                        :status="member.status"
                    />
                </div>

                <!-- Stats & Recent Activity (Right) -->
                <div class="md:col-span-7 space-y-6">
                    <!-- Subscription Status Box -->
                    <div class="bg-slate-900/60 border border-slate-800 p-6 rounded-3xl space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>📅</span>
                                <span>تفاصيل الاشتراك الحالي</span>
                            </h3>
                            <span v-if="subscription" class="text-xs font-mono font-bold text-amber-400">
                                ينتهي في: {{ subscription.end_date }}
                            </span>
                        </div>

                        <div v-if="subscription" class="grid grid-cols-2 gap-4 pt-2">
                            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80">
                                <span class="text-[11px] text-slate-400 block mb-1">الباقة المشترك بها</span>
                                <span class="text-sm font-bold text-white">{{ subscription.plan_name }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80">
                                <span class="text-[11px] text-slate-400 block mb-1">الأيام المتبقية</span>
                                <span class="text-xl font-black text-amber-400 font-mono">{{ subscription.remaining_days }} يوم</span>
                            </div>
                        </div>

                        <div v-else class="p-6 text-center rounded-2xl bg-slate-950/50 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">ليس لديك باقة تدريب نشطة حالياً</p>
                            <a href="/member/dashboard/subscriptions" class="inline-block mt-3 text-xs font-bold text-amber-400 hover:underline">
                                تصفح الباقات واشترك الآن ←
                            </a>
                        </div>
                    </div>

                    <!-- Recent Check-ins -->
                    <div class="bg-slate-900/60 border border-slate-800 p-6 rounded-3xl space-y-4">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>⏱️</span>
                            <span>آخر حركات تسجيل الدخول بالصالة</span>
                        </h3>

                        <div v-if="recentCheckIns.length > 0" class="space-y-2">
                            <div
                                v-for="checkIn in recentCheckIns"
                                :key="checkIn.id"
                                class="flex items-center justify-between p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-xs"
                            >
                                <span class="text-slate-300 font-mono">{{ checkIn.created_at }}</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="checkIn.status === 'valid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'"
                                >
                                    {{ checkIn.status === 'valid' ? 'دخول نظامي' : 'مرفوض' }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="text-center py-6 text-xs text-slate-500">
                            لا توجد حركات تسجيل دخول مسجلة بعد
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
