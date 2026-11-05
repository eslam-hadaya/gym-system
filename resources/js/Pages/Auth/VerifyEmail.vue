<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    email: {
        type: String,
        default: 'player@example.com',
    },
});

// مصفوفة لتخزين كل خانة من الـ 6 أرقام بشكل مستقل
const digits = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);

const form = useForm({
    code: '',
});

const resendForm = useForm({});

// مؤقت إعادة الإرسال (60 ثانية)
const timer = ref(60);
let intervalId = null;

const canResend = computed(() => timer.value === 0);

const startTimer = () => {
    timer.value = 60;
    if (intervalId) clearInterval(intervalId);
    intervalId = setInterval(() => {
        if (timer.value > 0) {
            timer.value--;
        } else {
            clearInterval(intervalId);
        }
    }, 1000);
};

onMounted(() => {
    startTimer();
    // التركيز على أول خانة عند الفتح
    if (inputRefs.value[0]) {
        inputRefs.value[0].focus();
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

// التعامل مع الكتابة والتنقل التلقائي للخانة التالية
const handleInput = (index, event) => {
    const val = event.target.value;
    // التأكد من قبول الأرقام فقط
    if (!/^\d*$/.test(val)) {
        digits.value[index] = '';
        return;
    }

    digits.value[index] = val.slice(-1); // أخذ الرقم الأخير فقط

    if (val && index < 5) {
        inputRefs.value[index + 1]?.focus();
    }

    // تجميع الكود وإرساله تلقائياً عند اكتمال الـ 6 خانات
    form.code = digits.value.join('');
    if (form.code.length === 6) {
        submitVerification();
    }
};

// التعامل مع زر الحذف (Backspace) للرجوع للخانة السابقة
const handleKeyDown = (index, event) => {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        inputRefs.value[index - 1]?.focus();
    }
};

// إمكانية لصق الرمز كاملاً دفعة واحدة (Paste)
const handlePaste = (event) => {
    event.preventDefault();
    const pasteData = event.clipboardData.getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
        pasteData.split('').forEach((char, idx) => {
            if (idx < 6) digits.value[idx] = char;
        });
        form.code = pasteData;
        inputRefs.value[5]?.focus();
        submitVerification();
    }
};

const submitVerification = () => {
    form.code = digits.value.join('');
    if (form.code.length !== 6) return;

    form.post('/verify-email', {
        onError: () => {
            // تصفير الخانات عند الخطأ وإعادة التركيز للأولى
            digits.value = ['', '', '', '', '', ''];
            inputRefs.value[0]?.focus();
        },
    });
};

const handleResend = () => {
    if (!canResend.value || resendForm.processing) return;

    resendForm.post('/email/verification-notification', {
        onSuccess: () => {
            startTimer();
        },
    });
};
</script>

<template>
    <Head title="تأكيد البريد الإلكتروني" />

    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center px-6 py-12 selection:bg-amber-500 selection:text-black">
        <!-- رابط الرجوع لتسجيل الدخول -->
        <div class="w-full max-w-md mb-6 flex justify-between items-center text-xs text-slate-400">
            <Link href="/login" class="hover:text-amber-400 flex items-center gap-1.5 transition">
                <span>←</span>
                <span>العودة لتسجيل الدخول</span>
            </Link>
            <span class="text-slate-600 font-mono">حماية وتفعيل الحساب</span>
        </div>

        <!-- بطاقة التحقق -->
        <div class="w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-3xl p-8 shadow-2xl space-y-6 relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>

            <!-- الترويسة -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl inline-flex items-center justify-center shadow-lg shadow-amber-500/20">
                    📩
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">رمز التحقق من البريد</h1>
                <p class="text-xs text-slate-400 leading-relaxed">
                    أرسلنا رمزاً مؤلفاً من 6 أرقام إلى بريدك الإلكتروني: <br />
                    <span class="text-amber-400 font-mono font-bold dir-ltr inline-block mt-1">{{ props.email }}</span>
                </p>
            </div>

            <!-- حقول إدخال الـ OTP الستة -->
            <div class="space-y-4">
                <div class="flex items-center justify-center gap-2 sm:gap-3 dir-ltr" @paste="handlePaste">
                    <input
                        v-for="(digit, idx) in digits"
                        :key="idx"
                        ref="el => inputRefs[idx] = el"
                        v-model="digits[idx]"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        :disabled="form.processing"
                        @input="handleInput(idx, $event)"
                        @keydown="handleKeyDown(idx, $event)"
                        :class="[
                            'w-11 h-14 sm:w-12 sm:h-16 text-center text-xl sm:text-2xl font-black bg-slate-950 border rounded-2xl text-white focus:outline-none transition-all shadow-inner',
                            form.errors.code
                                ? 'border-rose-500 text-rose-300'
                                : digits[idx]
                                    ? 'border-amber-500 ring-2 ring-amber-500/20 bg-slate-900'
                                    : 'border-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20'
                        ]"
                    />
                </div>

                <!-- رسالة الخطأ إن وُجدت -->
                <p v-if="form.errors.code" class="text-rose-400 text-xs text-center font-medium flex items-center justify-center gap-1">
                    <span>⚠️</span>
                    <span>{{ form.errors.code }}</span>
                </p>

                <!-- زر التأكيد اليدوي -->
                <button
                    type="button"
                    @click="submitVerification"
                    :disabled="form.processing || form.code.length !== 6"
                    class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl transition text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ form.processing ? 'جاري التحقق من الرمز...' : 'تأكيد الرمز وتفعيل الحساب' }}</span>
                </button>
            </div>

            <!-- خيار إعادة الإرسال والمؤقت -->
            <div class="text-center pt-4 border-t border-slate-800/80 space-y-2">
                <p class="text-xs text-slate-400">
                    لم يصلك الرمز في صندوق الوارد أو البريد غير الهام؟
                </p>

                <div v-if="!canResend" class="text-xs font-mono text-slate-500 flex items-center justify-center gap-1.5">
                    <span>⏳ يمكنك طلب رمز جديد بعد:</span>
                    <strong class="text-amber-400">{{ timer }} ثانية</strong>
                </div>

                <button
                    v-else
                    type="button"
                    @click="handleResend"
                    :disabled="resendForm.processing"
                    class="text-xs text-amber-400 hover:text-amber-300 font-bold hover:underline transition cursor-pointer"
                >
                    {{ resendForm.processing ? 'جاري إرسال الرمز مجدداً...' : 'إعادة إرسال رمز جديد الآن' }}
                </button>
            </div>
        </div>
    </div>
</template>
