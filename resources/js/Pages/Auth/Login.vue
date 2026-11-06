<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 relative overflow-hidden" dir="rtl">
        <div class="w-full max-w-md space-y-8 bg-slate-900/60 p-8 rounded-3xl border border-slate-800 backdrop-blur-xl shadow-2xl relative z-10">
            <div class="text-center space-y-2">
                <Link href="/" class="inline-flex items-center gap-2 text-amber-500 font-black text-2xl tracking-wider">
                    <span>🏋️</span>
                    <span>نادي الأبطال</span>
                </Link>
                <h2 class="text-xl font-bold text-white tracking-tight">تسجيل الدخول إلى النظام</h2>
                <p class="text-xs text-slate-400">سجل دخولك لمتابعة تدريبك أو إدارة الصالة الرياضية</p>
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-5">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">البريد الإلكتروني</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-3 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none"
                        :class="{ 'border-rose-500': form.errors.email }"
                        placeholder="name@example.com"
                    />
                    <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-medium">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-300">كلمة المرور</label>
                    </div>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none font-mono"
                        :class="{ 'border-rose-500': form.errors.password }"
                        placeholder="••••••••"
                    />
                    <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-medium">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-400 hover:text-slate-200">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 focus:ring-offset-0"
                        />
                        <span>تذكر بياناتي</span>
                    </label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl text-sm transition shadow-lg shadow-amber-500/10 cursor-pointer flex justify-center items-center gap-2"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>تسجيل الدخول</span>
                </button>

                <p class="text-center text-xs text-slate-400 pt-2">
                    ليس لديك حساب؟
                    <Link href="/register" class="text-amber-400 font-semibold hover:underline">سجل حساب جديد الآن</Link>
                </p>
            </form>
        </div>
    </div>
</template>
