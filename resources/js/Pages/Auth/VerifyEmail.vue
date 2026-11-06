<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    expiresInSeconds: {
        type: Number,
        default: 300,
    },
});

const digits = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);
const timeLeft = ref(props.expiresInSeconds);
let timer = null;

const form = useForm({
    code: '',
});

const isComplete = computed(() => digits.value.every((d) => d !== ''));

const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const handleInput = (index, event) => {
    const val = event.target.value.replace(/\D/g, '');
    digits.value[index] = val ? val.slice(-1) : '';

    if (val && index < 5) {
        inputRefs.value[index + 1]?.focus();
    }
};

const handleKeyDown = (index, event) => {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        inputRefs.value[index - 1]?.focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pasteData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (!pasteData) return;

    pasteData.split('').forEach((char, idx) => {
        digits.value[idx] = char;
    });

    const nextIndex = Math.min(pasteData.length, 5);
    inputRefs.value[nextIndex]?.focus();
};

const submit = () => {
    form.code = digits.value.join('');
    form.post('/verify-email', {
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            inputRefs.value[0]?.focus();
        },
    });
};

const resend = () => {
    if (timeLeft.value > 0) return;

    router.post('/verify-email/resend', {}, {
        onSuccess: () => {
            timeLeft.value = props.expiresInSeconds;
            digits.value = ['', '', '', '', '', ''];
            inputRefs.value[0]?.focus();
        },
    });
};

onMounted(() => {
    inputRefs.value[0]?.focus();
    timer = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 relative overflow-hidden" dir="rtl">
        <div class="w-full max-w-md space-y-8 bg-slate-900/60 p-8 rounded-3xl border border-slate-800 backdrop-blur-xl shadow-2xl relative z-10 text-center">
            <div class="space-y-2">
                <div class="w-14 h-14 bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-2xl flex items-center justify-center text-2xl mx-auto">
                    📬
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">تأكيد رمز التحقق</h2>
                <p class="text-xs text-slate-400">
                    أدخل الرمز المكون من 6 أرقام المرسل إلى بريدك:
                </p>
                <p class="text-xs font-mono font-bold text-amber-400 dir-ltr inline-block bg-slate-950 px-3 py-1 rounded-lg border border-slate-800">
                    {{ email }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- خانات إدخال الـ 6 أرقام -->
                <div class="flex justify-center gap-2 sm:gap-3 dir-ltr" @paste="handlePaste">
                    <input
                        v-for="(digit, idx) in digits"
                        :key="idx"
                        :ref="(el) => (inputRefs[idx] = el)"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        :value="digit"
                        @input="handleInput(idx, $event)"
                        @keydown="handleKeyDown(idx, $event)"
                        class="w-11 h-14 sm:w-12 sm:h-16 text-center text-xl font-mono font-bold rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition"
                        :class="{ 'border-rose-500 text-rose-400': form.errors.code }"
                    />
                </div>

                <p v-if="form.errors.code" class="text-xs text-rose-400 font-medium">
                    {{ form.errors.code }}
                </p>

                <!-- عداد الصلاحية (5 دقائق) -->
                <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                    <span>صلاحية الرمز تنتهي خلال:</span>
                    <span class="font-mono font-bold text-amber-400" :class="{ 'text-rose-500 animate-pulse': timeLeft < 60 }">
                        {{ formattedTime }}
                    </span>
                </div>

                <button
                    type="submit"
                    :disabled="!isComplete || form.processing"
                    class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-slate-950 font-bold rounded-xl text-sm transition shadow-lg shadow-amber-500/10 cursor-pointer flex justify-center items-center gap-2"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>تأكيد الرمز والدخول</span>
                </button>
            </form>

            <div class="pt-2 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-center gap-2">
                <span>لم يصلك الرمز؟</span>
                <button
                    type="button"
                    @click="resend"
                    :disabled="timeLeft > 0"
                    class="text-amber-400 font-bold hover:underline disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                >
                    إعادة إرسال رمز جديد
                </button>
            </div>
        </div>
    </div>
</template>
