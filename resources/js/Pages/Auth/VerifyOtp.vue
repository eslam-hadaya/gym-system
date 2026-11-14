<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from "vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { MailCheck, Loader2, RotateCcw, LogOut, Check } from "@lucide/vue";

const props = defineProps({
    email: { type: String, default: "" },
});

const form = useForm({ code: "" });

const inputs = ref([]); // refs للخانات الستة
const digits = ref(["", "", "", "", "", ""]);
const localError = ref("");
const resendSuccess = ref(false);
const isResending = ref(false);
const resendCooldown = ref(0);
let cooldownTimer = null;
let successTimer = null;

const fullCode = computed(() => digits.value.join(""));
const isComplete = computed(() => fullCode.value.length === 6);

const displayError = computed(() => localError.value || form.errors.code || "");

// ====== إدارة الخانات ======
const focusInput = (idx) => {
    nextTick(() => inputs.value[idx]?.focus());
};

const handleInput = (idx, e) => {
    const value = e.target.value.replace(/\D/g, "");

    // لو المستخدم لصق كود كامل في خانة واحدة
    if (value.length > 1) {
        distribute(value, idx);
        return;
    }

    digits.value[idx] = value;

    if (value && idx < 5) {
        focusInput(idx + 1);
    }

    syncForm();
};

const handleKeydown = (idx, e) => {
    if (e.key === "Backspace" && !digits.value[idx] && idx > 0) {
        focusInput(idx - 1);
        digits.value[idx - 1] = "";
        syncForm();
    }
    if (e.key === "ArrowLeft" && idx > 0) focusInput(idx - 1);
    if (e.key === "ArrowRight" && idx < 5) focusInput(idx + 1);
};

const handlePaste = (e) => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData)
        .getData("text")
        .replace(/\D/g, "");
    if (!pasted) return;
    distribute(pasted, 0);
};

const distribute = (text, startIdx = 0) => {
    const chars = text.slice(0, 6 - startIdx).split("");
    chars.forEach((ch, i) => {
        if (startIdx + i < 6) digits.value[startIdx + i] = ch;
    });
    const nextEmpty = digits.value.findIndex((d) => !d);
    focusInput(nextEmpty === -1 ? 5 : nextEmpty);
    syncForm();
};

const syncForm = () => {
    form.code = fullCode.value;
};

const clearAll = () => {
    digits.value = ["", "", "", "", "", ""];
    form.code = "";
    localError.value = "";
    focusInput(0);
};

// ====== الإرسال ======
const submitOtp = () => {
    localError.value = "";

    if (!isComplete.value) {
        localError.value = "يرجى إدخال رمز التحقق المكوّن من 6 أرقام كاملاً.";
        return;
    }

    form.post("/verify-email", {
        preserveScroll: true,
        onError: (errors) => {
            localError.value =
                errors.code ||
                Object.values(errors)[0] ||
                "رمز التحقق غير صحيح أو منتهي الصلاحية.";
            clearAll();
        },
    });
};

// ====== إعادة الإرسال ======
const startCooldown = (seconds = 60) => {
    resendCooldown.value = seconds;
    cooldownTimer = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) {
            clearInterval(cooldownTimer);
            cooldownTimer = null;
        }
    }, 1000);
};

const resendOtp = () => {
    if (resendCooldown.value > 0) return;

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
                startCooldown(60);
                successTimer = setTimeout(() => {
                    resendSuccess.value = false;
                }, 5000);
            },
            onError: (errors) => {
                localError.value =
                    Object.values(errors)[0] || "تعذر إعادة إرسال الرمز حالياً.";
            },
            onFinish: () => {
                isResending.value = false;
            },
        },
    );
};

onMounted(() => {
    focusInput(0);
});

onBeforeUnmount(() => {
    if (cooldownTimer) clearInterval(cooldownTimer);
    if (successTimer) clearTimeout(successTimer);
});
</script>

<template>
    <Head title="تأكيد البريد الإلكتروني" />

    <div
        dir="rtl"
        class="flex min-h-screen items-center justify-center bg-surface-base px-4 py-10 sm:px-6 transition-colors duration-300"
    >
        <div
            class="w-full max-w-md space-y-6 rounded-3xl border border-line-soft bg-surface-elevated p-6 shadow-sm sm:p-8"
        >
            <!-- ============ الرأس ============ -->
            <div class="space-y-3 text-center">
                <span
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-success-500/30 bg-success-500/10 text-success-500"
                >
                    <MailCheck class="h-6 w-6" :stroke-width="2" />
                </span>

                <h1 class="text-lg font-black text-ink-primary sm:text-xl">
                    التحقق من البريد الإلكتروني
                </h1>

                <p
                    class="text-xs leading-relaxed text-ink-secondary sm:text-sm"
                >
                    أدخل رمز التأكيد المكوّن من 6 أرقام المرسل إلى بريدك
                    <span
                        v-if="email"
                        class="mt-1 block font-mono text-[11px] font-bold text-success-600 dark:text-success-400"
                        dir="ltr"
                    >
                        {{ email }}
                    </span>
                </p>
            </div>

            <!-- ============ الأخطاء ============ -->
            <div
                v-if="displayError"
                role="alert"
                aria-live="polite"
                class="rounded-xl border border-danger-500/30 bg-danger-500/10 p-3 text-center text-xs font-semibold text-danger-500"
            >
                {{ displayError }}
            </div>

            <!-- ============ نجاح الإرسال ============ -->
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="resendSuccess"
                    role="status"
                    class="flex items-center justify-center gap-2 rounded-xl border border-success-500/30 bg-success-500/10 p-3 text-xs font-semibold text-success-600 dark:text-success-400"
                >
                    <Check class="h-4 w-4" :stroke-width="2.5" />
                    تم إرسال رمز تحقق جديد بنجاح
                </div>
            </Transition>

            <!-- ============ النموذج ============ -->
            <form @submit.prevent="submitOtp" class="space-y-5">
                <div>
                    <label
                        class="mb-3 block text-center text-xs font-semibold text-ink-secondary"
                    >
                        رمز التحقق (OTP)
                    </label>

                    <!-- خانات الـ OTP -->
                    <div
                        dir="ltr"
                        class="flex justify-center gap-2 sm:gap-2.5"
                        @paste="handlePaste"
                    >
                        <input
                            v-for="(digit, idx) in digits"
                            :key="idx"
                            :ref="(el) => (inputs[idx] = el)"
                            :value="digit"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            autocomplete="one-time-code"
                            :aria-label="`الرقم ${idx + 1} من رمز التحقق`"
                            class="h-12 w-10 rounded-xl border-2 bg-surface-base text-center text-xl font-black text-ink-primary transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 sm:h-14 sm:w-12 sm:text-2xl"
                            :class="
                                displayError
                                    ? 'border-danger-500'
                                    : digit
                                      ? 'border-brand-500'
                                      : 'border-line-strong'
                            "
                            @input="handleInput(idx, $event)"
                            @keydown="handleKeydown(idx, $event)"
                            @focus="$event.target.select()"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || !isComplete"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
                >
                    <Loader2
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin"
                        :stroke-width="2.5"
                    />
                    <span>
                        {{
                            form.processing
                                ? "جاري التحقق..."
                                : "تأكيد الرمز والدخول"
                        }}
                    </span>
                </button>
            </form>

            <!-- ============ الأسفل ============ -->
            <div
                class="flex items-center justify-between gap-3 border-t border-line-soft pt-4 text-xs"
            >
                <button
                    type="button"
                    @click="resendOtp"
                    :disabled="isResending || resendCooldown > 0"
                    class="inline-flex items-center gap-1.5 font-bold text-success-600 hover:underline disabled:cursor-not-allowed disabled:opacity-50 dark:text-success-400"
                >
                    <RotateCcw class="h-3.5 w-3.5" :stroke-width="2.5" />
                    <span v-if="isResending">جارٍ الإرسال...</span>
                    <span v-else-if="resendCooldown > 0">
                        إعادة الإرسال بعد {{ resendCooldown }}ث
                    </span>
                    <span v-else>إعادة إرسال الرمز</span>
                </button>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="inline-flex items-center gap-1.5 font-medium text-ink-muted transition-colors hover:text-danger-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400 rounded"
                >
                    <LogOut class="h-3.5 w-3.5" :stroke-width="2" />
                    تسجيل الخروج
                </Link>
            </div>
        </div>
    </div>
</template>
