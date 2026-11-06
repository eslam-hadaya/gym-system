<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    currentSubscription: {
        type: Object,
        default: null,
    },
    memberStatus: {
        type: String,
        default: 'pending',
    },
});

const selectedPlan = ref(props.plans[0] || null);
const paymentMethod = ref('shamcash');

const form = useForm({
    plan_id: props.plans[0]?.id || null,
    payment_method: 'shamcash',
    transaction_ref: '',
});

const selectPlan = (plan) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
};

const submitSubscription = () => {
    form.payment_method = paymentMethod.value;
    form.post('/member/dashboard/subscriptions', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('transaction_ref');
        },
    });
};
</script>

<template>
    <Head title="اشتراكي وتجديد الباقة" />

    <MemberLayout>
        <div class="space-y-8" dir="rtl">
            <!-- الترويسة -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">إدارة الاشتراك وتجديد الباقة</h1>
                    <p class="text-xs text-slate-400 mt-1">تتبع صلاحية عضويتك الرياضية أو اختر باقة جديدة وسدد عبر شام كاش أو نقداً</p>
                </div>
            </div>

            <!-- بطاقة حالة الاشتراك الحالي إن وجدت -->
            <div v-if="currentSubscription" class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-3 h-3 rounded-full"
                            :class="currentSubscription.status === 'verified' ? 'bg-emerald-500 animate-pulse' : (currentSubscription.status === 'pending' ? 'bg-amber-500' : 'bg-rose-500')"
                        ></span>
                        <h2 class="text-base font-bold text-white">الاشتراك الحالي: {{ currentSubscription.plan_name }}</h2>
                    </div>

                    <span
                        class="text-xs px-3 py-1 rounded-xl font-bold font-mono border"
                        :class="currentSubscription.status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (currentSubscription.status === 'pending' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20')"
                    >
                        {{ currentSubscription.status === 'verified' ? 'سارٍ ومفعل' : (currentSubscription.status === 'pending' ? 'بانتظار تأكيد الدفع' : 'منتهي الصلاحية') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-1">تاريخ البدء والانتهاء:</span>
                        <p class="font-mono text-slate-200">{{ currentSubscription.start_date }} ➔ {{ currentSubscription.end_date }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">طريقة السداد:</span>
                        <p class="font-bold text-amber-400">{{ currentSubscription.method === 'shamcash' ? 'حوالة شام كاش' : 'نقداً في الصالة' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">رقم الإشعار / المرجع:</span>
                        <p class="font-mono text-slate-300">{{ currentSubscription.ref || 'لا يوجد' }}</p>
                    </div>
                </div>
            </div>

            <!-- تنبيه في حال لم يكن مشتركاً بعد -->
            <div v-else class="p-6 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h3 class="text-sm font-bold text-amber-400">حسابك غير مفعل بعد</h3>
                    <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                        اختر الباقة المناسبة لك من الأسفل وحدد طريقة الدفع لتفعيل بطاقة دخول الـ QR الخاصة بك فور مطابقة الدفع.
                    </p>
                </div>
            </div>

            <!-- قسم اختيار الباقة وطريقة السداد -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-black text-white">1. اختر الباقة الرياضية</h2>
                    <p class="text-xs text-slate-400 mt-1">تحدد المدة وصلاحية استخدام مرافق النادي</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="selectPlan(plan)"
                        :class="[
                            'p-5 rounded-2xl border transition duration-150 cursor-pointer flex flex-col justify-between space-y-4 relative',
                            selectedPlan?.id === plan.id
                                ? 'bg-amber-500/10 border-amber-500 shadow-lg shadow-amber-500/10'
                                : 'bg-slate-900/60 border-slate-800 hover:border-slate-700'
                        ]"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold text-amber-400">{{ plan.days }} يوماً</span>
                                <span v-if="selectedPlan?.id === plan.id" class="text-amber-400 text-sm font-bold">✓ محدد</span>
                            </div>
                            <h3 class="text-base font-bold text-white">{{ plan.name }}</h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-black text-white font-mono">{{ Number(plan.price).toLocaleString() }}</span>
                                <span class="text-xs text-slate-400">ل.س</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. طريقة الدفع وتفاصيل الحوالة -->
                <div class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 space-y-6">
                    <div>
                        <h2 class="text-lg font-black text-white">2. تحديد وسيلة الدفع</h2>
                        <p class="text-xs text-slate-400 mt-1">سدد عبر شام كاش أو مباشرة نقداً عند الكابتن في الصالة</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label
                            :class="[
                                'p-4 rounded-2xl border flex items-center justify-between cursor-pointer transition',
                                paymentMethod === 'shamcash'
                                    ? 'bg-amber-500/10 border-amber-500 text-white'
                                    : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <input type="radio" v-model="paymentMethod" value="shamcash" class="text-amber-500 focus:ring-0" />
                                <div>
                                    <p class="text-xs font-bold text-white">شام كاش (Sham Cash)</p>
                                    <span class="text-[10px] text-slate-400">تحويل إلكتروني فوري</span>
                                </div>
                            </div>
                            <span class="text-lg">📲</span>
                        </label>

                        <label
                            :class="[
                                'p-4 rounded-2xl border flex items-center justify-between cursor-pointer transition',
                                paymentMethod === 'cash'
                                    ? 'bg-amber-500/10 border-amber-500 text-white'
                                    : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <input type="radio" v-model="paymentMethod" value="cash" class="text-amber-500 focus:ring-0" />
                                <div>
                                    <p class="text-xs font-bold text-white">نقداً في الصالة (Cash Desk)</p>
                                    <span class="text-[10px] text-slate-400">الدفع عند مكتب الاستقبال</span>
                                </div>
                            </div>
                            <span class="text-lg">💵</span>
                        </label>
                    </div>

                    <!-- تعليمات شام كاش في حال تم اختياره -->
                    <div v-if="paymentMethod === 'shamcash'" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <p class="text-xs text-slate-300 leading-relaxed">
                            قم بتحويل المبلغ المطلوب إلى رقم حساب شام كاش التابع للنادي:
                            <strong class="font-mono text-amber-400 text-sm mr-1">0988001122</strong>،
                            ثم أدخل رقم العملية أو الإشعار في الحقل أدناه ليتم التحقق منه في لوحة الإدارة.
                        </p>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">رقم الإشعار / رمز العملية (TXN Ref)</label>
                            <input
                                v-model="form.transaction_ref"
                                type="text"
                                placeholder="مثال: TXN-492109"
                                class="w-full sm:max-w-md px-4 py-2.5 bg-slate-900 border border-slate-800 focus:border-amber-500 rounded-xl text-xs font-mono text-white outline-none transition uppercase"
                                :class="{ 'border-rose-500': form.errors.transaction_ref }"
                            />
                            <p v-if="form.errors.transaction_ref" class="text-xs text-rose-400 mt-1">{{ form.errors.transaction_ref }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="button"
                            @click="submitSubscription"
                            :disabled="form.processing || !selectedPlan"
                            class="px-8 py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shadow-lg shadow-amber-500/20"
                        >
                            <span v-if="form.processing" class="w-3.5 h-3.5 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                            <span>إرسال طلب الاشتراك</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
