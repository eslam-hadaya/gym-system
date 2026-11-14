<script setup>
import { useForm, Link, Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    Dumbbell,
    Loader2,
    Eye,
    EyeOff,
    User,
    Mail,
    Phone,
    Lock,
    Users,
    UserCircle2,
    AlertCircle,
} from "@lucide/vue";
import PasswordStrength from "./PasswordStrength.vue";

const form = useForm({
    name: "",
    email: "",
    phone: "",
    gender: "male",
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);
const showConfirm = ref(false);

const passwordsMatch = computed(() => {
    if (!form.password_confirmation) return null;
    return form.password === form.password_confirmation;
});

const passwordsMismatch = computed(() => passwordsMatch.value === false);

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

// تنسيق رقم الهاتف السوري: 0912 345 678
const formatPhone = (value) => {
    const digits = value.replace(/\D/g, "").slice(0, 10);
    if (digits.length <= 4) return digits;
    if (digits.length <= 7) return `${digits.slice(0, 4)} ${digits.slice(4)}`;
    return `${digits.slice(0, 4)} ${digits.slice(4, 7)} ${digits.slice(7)}`;
};

const onPhoneInput = (e) => {
    form.phone = formatPhone(e.target.value);
};

const submit = () => {
    if (passwordsMismatch.value) return;
    form.post("/register", {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};

const genderOptions = [
    { value: "male", label: "فترة الرجال", sub: "مسائي", icon: Users },
    { value: "female", label: "فترة السيدات", sub: "صباحي", icon: UserCircle2 },
];
</script>

<template>
    <Head title="إنشاء حساب جديد" />

    <div
        dir="rtl"
        class="flex min-h-screen items-center justify-center bg-surface-base px-4 py-10 sm:px-6 transition-colors duration-300"
    >
        <div
            class="w-full max-w-lg space-y-6 rounded-3xl border border-line-soft bg-surface-elevated p-6 shadow-sm sm:p-8"
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
                    إنشاء حساب مشترك جديد
                </h2>
                <p class="text-xs text-ink-secondary sm:text-sm">
                    انضم للنادي واحصل على كود عضويتك وبطاقتك الرقمية فوراً
                </p>
            </div>

            <!-- ============ ملخص الأخطاء ============ -->
            <div
                v-if="hasErrors"
                role="alert"
                class="flex items-start gap-2 rounded-xl border border-danger-500/30 bg-danger-500/10 p-3 text-xs text-danger-500"
            >
                <AlertCircle
                    class="mt-0.5 h-4 w-4 shrink-0"
                    :stroke-width="2"
                />
                <p>يرجى تصحيح الأخطاء أدناه للمتابعة.</p>
            </div>

            <!-- ============ النموذج ============ -->
            <form @submit.prevent="submit" class="space-y-4" novalidate>
                <!-- الاسم -->
                <div class="space-y-1.5">
                    <label
                        for="name"
                        class="text-xs font-semibold text-ink-secondary"
                    >
                        الاسم الثلاثي
                    </label>
                    <div class="relative">
                        <User
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                            :stroke-width="2"
                        />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="محمد أحمد علي"
                            :aria-invalid="!!form.errors.name"
                            class="w-full rounded-xl border bg-surface-base pr-10 pl-4 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                            :class="
                                form.errors.name
                                    ? 'border-danger-500'
                                    : 'border-line-strong'
                            "
                        />
                    </div>
                    <p
                        v-if="form.errors.name"
                        role="alert"
                        class="text-[11px] font-medium text-danger-500"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- البريد + الهاتف -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label
                            for="email"
                            class="text-xs font-semibold text-ink-secondary"
                        >
                            البريد الإلكتروني
                        </label>
                        <div class="relative">
                            <Mail
                                class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                                :stroke-width="2"
                            />
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                inputmode="email"
                                required
                                autocomplete="email"
                                placeholder="name@example.com"
                                :aria-invalid="!!form.errors.email"
                                class="w-full rounded-xl border bg-surface-base pr-10 pl-4 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                                :class="
                                    form.errors.email
                                        ? 'border-danger-500'
                                        : 'border-line-strong'
                                "
                            />
                        </div>
                        <p
                            v-if="form.errors.email"
                            role="alert"
                            class="text-[11px] font-medium text-danger-500"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="phone"
                            class="text-xs font-semibold text-ink-secondary"
                        >
                            رقم الهاتف
                        </label>
                        <div class="relative">
                            <Phone
                                class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                                :stroke-width="2"
                            />
                            <input
                                id="phone"
                                :value="form.phone"
                                @input="onPhoneInput"
                                type="tel"
                                inputmode="tel"
                                required
                                autocomplete="tel"
                                placeholder="0912 345 678"
                                dir="ltr"
                                :aria-invalid="!!form.errors.phone"
                                class="w-full rounded-xl border bg-surface-base pr-10 pl-4 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                                :class="
                                    form.errors.phone
                                        ? 'border-danger-500'
                                        : 'border-line-strong'
                                "
                            />
                        </div>
                        <p
                            v-if="form.errors.phone"
                            role="alert"
                            class="text-[11px] font-medium text-danger-500"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>
                </div>

                <!-- فترة التدريب -->
                <div class="space-y-1.5">
                    <span class="text-xs font-semibold text-ink-secondary">
                        فترة التدريب
                    </span>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            v-for="option in genderOptions"
                            :key="option.value"
                            class="flex cursor-pointer items-center gap-2 rounded-xl border p-3 text-xs font-bold transition-colors duration-150 focus-within:ring-2 focus-within:ring-brand-300"
                            :class="
                                form.gender === option.value
                                    ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400'
                                    : 'border-line-strong bg-surface-base text-ink-secondary hover:border-brand-500/40'
                            "
                        >
                            <input
                                type="radio"
                                v-model="form.gender"
                                :value="option.value"
                                class="sr-only"
                            />
                            <component
                                :is="option.icon"
                                class="h-4 w-4 shrink-0"
                                :stroke-width="2"
                            />
                            <span class="flex flex-col leading-tight">
                                <span>{{ option.label }}</span>
                                <span
                                    class="text-[10px] font-normal opacity-70"
                                    >{{ option.sub }}</span
                                >
                            </span>
                        </label>
                    </div>
                </div>

                <!-- كلمة المرور + التأكيد -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label
                            for="password"
                            class="text-xs font-semibold text-ink-secondary"
                        >
                            كلمة المرور
                        </label>
                        <div class="relative">
                            <Lock
                                class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                                :stroke-width="2"
                            />
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                :aria-invalid="!!form.errors.password"
                                class="w-full rounded-xl border bg-surface-base pr-10 pl-10 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
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
                                class="absolute left-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                            >
                                <Eye
                                    v-if="!showPassword"
                                    class="h-4 w-4"
                                    :stroke-width="2"
                                />
                                <EyeOff
                                    v-else
                                    class="h-4 w-4"
                                    :stroke-width="2"
                                />
                            </button>
                        </div>
                        <PasswordStrength :password="form.password" />
                        <p
                            v-if="form.errors.password"
                            role="alert"
                            class="text-[11px] font-medium text-danger-500"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="password_confirmation"
                            class="text-xs font-semibold text-ink-secondary"
                        >
                            تأكيد كلمة المرور
                        </label>
                        <div class="relative">
                            <Lock
                                class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                                :stroke-width="2"
                            />
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                :aria-invalid="passwordsMismatch"
                                class="w-full rounded-xl border bg-surface-base pr-10 pl-10 py-3 text-sm text-ink-primary placeholder:text-ink-muted transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500"
                                :class="
                                    passwordsMismatch
                                        ? 'border-danger-500'
                                        : passwordsMatch
                                          ? 'border-emerald-500'
                                          : 'border-line-strong'
                                "
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                :aria-label="
                                    showConfirm
                                        ? 'إخفاء كلمة المرور'
                                        : 'إظهار كلمة المرور'
                                "
                                class="absolute left-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                            >
                                <Eye
                                    v-if="!showConfirm"
                                    class="h-4 w-4"
                                    :stroke-width="2"
                                />
                                <EyeOff
                                    v-else
                                    class="h-4 w-4"
                                    :stroke-width="2"
                                />
                            </button>
                        </div>
                        <p
                            v-if="passwordsMismatch"
                            role="alert"
                            class="text-[11px] font-medium text-danger-500"
                        >
                            كلمتا المرور غير متطابقتين
                        </p>
                        <p
                            v-else-if="passwordsMatch"
                            class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            ✓ كلمتا المرور متطابقتان
                        </p>
                    </div>
                </div>

                <!-- زر الإرسال -->
                <button
                    type="submit"
                    :disabled="form.processing || passwordsMismatch"
                    class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
                >
                    <Loader2
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin"
                        :stroke-width="2.5"
                    />
                    <span>{{
                        form.processing
                            ? "جاري إنشاء الحساب..."
                            : "إنشاء الحساب"
                    }}</span>
                </button>

                <!-- رابط تسجيل الدخول -->
                <p class="text-center text-xs text-ink-secondary">
                    لديك حساب بالفعل؟
                    <Link
                        href="/login"
                        class="font-semibold text-brand-600 hover:underline dark:text-brand-400"
                    >
                        سجل الدخول هنا
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
