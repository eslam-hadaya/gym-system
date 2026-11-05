<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

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
    <Head title="تسجيل الدخول الموحد" />

    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center px-6 py-12 selection:bg-amber-500 selection:text-black">
        <!-- رابط العودة السريع للصفحة الرئيسية -->
        <div class="w-full max-w-md mb-6 flex justify-between items-center text-xs text-slate-400">
            <Link href="/" class="hover:text-amber-400 flex items-center gap-1.5 transition">
                <span>←</span>
                <span>العودة للرئيسية</span>
            </Link>
            <span class="text-slate-600 font-mono">بوابة الدخول الذكية</span>
        </div>

        <!-- صندوق تسجيل الدخول -->
        <div class="w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-3xl p-8 shadow-2xl space-y-6 relative overflow-hidden">
            <!-- لمسة إضاءة علوية -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>

            <!-- الترويسة والشعار -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl inline-flex items-center justify-center shadow-lg shadow-amber-500/20">
                    🏋️
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">تسجيل الدخول</h1>
                <p class="text-xs text-slate-400 leading-relaxed">
                    منصة الدخول الموحدة للمدراء، الكباتن، واللاعبين
                </p>
            </div>

            <!-- نموذج البيانات -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- حقل البريد الإلكتروني -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        البريد الإلكتروني
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        :class="[
                            'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition',
                            form.errors.email
                                ? 'border-rose-500 focus:border-rose-500 text-rose-200'
                                : 'border-slate-800 focus:border-amber-500'
                        ]"
                        placeholder="name@example.com"
                    />
                    <p v-if="form.errors.email" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1 font-medium">
                        <span>⚠️</span>
                        <span>{{ form.errors.email }}</span>
                    </p>
                </div>

                <!-- حقل كلمة المرور -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300">
                            كلمة المرور
                        </label>
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="text-[11px] text-slate-400 hover:text-amber-400 transition"
                        >
                            {{ showPassword ? 'إخفاء' : 'إظهار' }}
                        </button>
                    </div>

                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            :class="[
                                'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition',
                                form.errors.password
                                    ? 'border-rose-500 focus:border-rose-500 text-rose-200'
                                    : 'border-slate-800 focus:border-amber-500'
                            ]"
                            placeholder="••••••••"
                        />
                    </div>
                    <p v-if="form.errors.password" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1 font-medium">
                        <span>⚠️</span>
                        <span>{{ form.errors.password }}</span>
                    </p>
                </div>

                <!-- تذكرني -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-400 hover:text-slate-200">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        />
                        <span>تذكر بيانات دخولي</span>
                    </label>
                </div>

                <!-- زر الإرسال -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl transition text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ form.processing ? 'جاري التحقق من الحساب...' : 'دخول إلى النظام' }}</span>
                </button>
            </form>

            <!-- رابط تسجيل لاعب جديد -->
            <div class="text-center pt-4 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    لاعب جديد وترغب بالانضمام؟
                    <Link href="/register" class="text-amber-400 hover:underline font-bold mr-1">
                        سجل حسابك هنا
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
