<script setup>
import { ref } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    email: {
        type: String,
        default: "",
    },
});

const form = useForm({
    code: "",
});

const localError = ref("");
const resendSuccess = ref(false);
const isResending = ref(false);

const submitOtp = () => {
    localError.value = "";

    if (!form.code || form.code.trim().length !== 6) {
        localError.value = "يرجى إدخال رمز التحقق المكون من 6 أرقام كاملاً.";
        return;
    }

    form.post("/verify-email", {
        preserveScroll: true,
        onSuccess: () => {
            // التوجيه يتم تلقائياً من السيرفر عبر redirectByRole
        },
        onError: (errors) => {
            console.error("Validation errors:", errors);
            localError.value =
                errors.code ||
                Object.values(errors)[0] ||
                "رمز التحقق غير صحيح أو منتهي الصلاحية.";
        },
    });
};

const resendOtp = () => {
    isResending.value = true;
    resendSuccess.value = false;
    localError.value = "";

    router.post(
        "/verify-email/resend",
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                resendSuccess.value = true;
            },
            onError: (errors) => {
                localError.value =
                    Object.values(errors)[0] ||
                    "تعذر إعادة إرسال الرمز حالياً.";
            },
            onFinish: () => {
                isResending.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="تأكيد البريد الإلكتروني" />

    <div
        class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4"
    >
        <div
            class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-white space-y-6"
        >
            <!-- الأيقونة والترويسة -->
            <div class="text-center space-y-2">
                <div
                    class="w-14 h-14 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl flex items-center justify-center text-2xl mx-auto font-black"
                >
                    ✉️
                </div>
                <h1 class="text-xl font-black tracking-tight">
                    التحقق من البريد الإلكتروني
                </h1>
                <p class="text-xs text-slate-400 leading-relaxed">
                    أدخل رمز التأكيد المكون من 6 أرقام المرسل إلى بريدك:
                    <span
                        v-if="email"
                        class="font-mono text-emerald-400 font-bold block mt-1 dir-ltr"
                        >{{ email }}</span
                    >
                </p>
            </div>

            <!-- تنبيهات الخطأ والنجاح -->
            <div
                v-if="localError || form.errors.code"
                class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold rounded-xl text-center"
            >
                {{ localError || form.errors.code }}
            </div>

            <div
                v-if="resendSuccess"
                class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-xl text-center"
            >
                تم إرسال رمز تحقق جديد بنجاح! تفقد بريدك أو ملف السجلات.
            </div>

            <!-- نموذج إدخال الرمز -->
            <form @submit.prevent="submitOtp" class="space-y-4">
                <div>
                    <label
                        class="block text-xs font-bold text-slate-300 mb-2 text-center"
                        >رمز التحقق (OTP)</label
                    >
                    <input
                        v-model="form.code"
                        type="text"
                        maxlength="6"
                        placeholder="123456"
                        autocomplete="one-time-code"
                        class="w-full py-3.5 px-4 bg-slate-950 border-2 border-slate-800 rounded-2xl text-center text-2xl font-mono font-black tracking-[0.4em] text-emerald-400 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-700 placeholder:tracking-widest"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-950/50 transition-all disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span
                        v-if="form.processing"
                        class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
                    ></span>
                    تأكيد الرمز والدخول
                </button>
            </form>

            <!-- إعادة إرسال الكود أو تسجيل خروج -->
            <div
                class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs"
            >
                <button
                    type="button"
                    @click="resendOtp"
                    :disabled="isResending"
                    class="text-emerald-400 hover:underline font-bold disabled:opacity-50 cursor-pointer"
                >
                    {{ isResending ? "جارٍ الإرسال..." : "إعادة إرسال الرمز؟" }}
                </button>

                <form action="/logout" method="POST" class="inline">
                    <button
                        type="submit"
                        class="text-slate-400 hover:text-slate-200"
                    >
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
