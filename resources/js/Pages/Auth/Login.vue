<script setup>
import { useForm, Link, Head } from "@inertiajs/vue3";
import { Dumbbell, Loader2, Eye, EyeOff } from "@lucide/vue";
import { ref } from "vue";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post("/login", {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="تسجيل الدخول" />

    <div
        dir="rtl"
        class="flex min-h-screen items-center justify-center bg-surface-base px-4 py-10 sm:px-6 transition-colors duration-300"
    >
        <div
            class="w-full max-w-md space-y-7 rounded-3xl border border-line-soft bg-surface-elevated p-6 shadow-sm sm:p-8"
        >
            <!-- ============ الرأس ============ -->
            <div class="space-y-2 text-center">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-lg font-black text-brand-600 dark:text-brand-400"
                    aria-label="نادي الأبطال — الرئيسية"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-white shadow-glow"
                    >
                        <Dumbbell class="h-5 w-5" :stroke-width="2.2" />
                    </span>
                    <span>نادي الأبطال</span>
                </Link>

                <h2 class="text-lg font-bold text-ink-primary sm:text-xl">
                    تسجيل الدخول إلى النظام
                </h2>
                <p class="text-xs text-ink-secondary sm:text-sm">
                    سجل دخولك لمتابعة تدريبك أو إدارة الصالة الرياضية
                </p>
            </div>

            <!-- ============ النموذج ============ -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- البريد -->
                <div class="space-y-1.5">
                    <label
                        for="email"
                        class="text-xs font-semibold text-ink-secondary"
                    >
                        البريد الإلكتروني
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@example.com"
                        :aria-invalid="!!form.errors.email"
                        :aria-describedby="
                            form.errors.email ? 'email-error' : undefined
                        "
                        class="w-full rounded-xl border bg-surface-base px-4 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                        :class="
                            form.errors.email
                                ? 'border-danger-500'
                                : 'border-line-strong'
                        "
                    />
                    <p
                        v-if="form.errors.email"
                        id="email-error"
                        role="alert"
                        class="text-[11px] font-medium text-danger-500"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- كلمة المرور -->
                <div class="space-y-1.5">
                    <label
                        for="password"
                        class="text-xs font-semibold text-ink-secondary"
                    >
                        كلمة المرور
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            :aria-invalid="!!form.errors.password"
                            :aria-describedby="
                                form.errors.password
                                    ? 'password-error'
                                    : undefined
                            "
                            class="w-full rounded-xl border bg-surface-base px-4 py-3 pl-11 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                            :class="
                                form.errors.password
                                    ? 'border-danger-500'
                                    : 'border-line-strong'
                            "
                        />

                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="
                                showPassword
                                    ? 'إخفاء كلمة المرور'
                                    : 'إظهار كلمة المرور'
                            "
                            class="absolute left-2 top-1/2 -translate-y-1/2 inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                        >
                            <Eye
                                v-if="!showPassword"
                                class="h-4 w-4"
                                :stroke-width="2"
                            />
                            <EyeOff v-else class="h-4 w-4" :stroke-width="2" />
                        </button>
                    </div>
                    <p
                        v-if="form.errors.password"
                        id="password-error"
                        role="alert"
                        class="text-[11px] font-medium text-danger-500"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- تذكرني -->
                <div class="flex items-center justify-between">
                    <label
                        for="remember"
                        class="inline-flex cursor-pointer items-center gap-2 text-xs text-ink-secondary"
                    >
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-line-strong bg-surface-base text-brand-500 focus:ring-2 focus:ring-brand-300 focus:ring-offset-0"
                        />
                        <span>تذكر بياناتي</span>
                    </label>
                </div>

                <!-- زر الإرسال -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
                >
                    <Loader2
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin"
                        :stroke-width="2.5"
                    />
                    <span>{{
                        form.processing ? "جاري الدخول..." : "تسجيل الدخول"
                    }}</span>
                </button>

                <!-- رابط التسجيل -->
                <p class="pt-1 text-center text-xs text-ink-secondary">
                    ليس لديك حساب؟
                    <Link
                        href="/register"
                        class="font-semibold text-brand-600 hover:underline dark:text-brand-400"
                    >
                        سجل حساب جديد الآن
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
