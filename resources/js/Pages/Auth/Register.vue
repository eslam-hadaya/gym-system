<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    gender: 'male',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="تسجيل لاعب جديد" />

    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center px-6 py-12 selection:bg-amber-500 selection:text-black">
        <!-- شريط الرجوع العلوي -->
        <div class="w-full max-w-lg mb-6 flex justify-between items-center text-xs text-slate-400">
            <Link href="/" class="hover:text-amber-400 flex items-center gap-1.5 transition">
                <span>←</span>
                <span>العودة للرئيسية</span>
            </Link>
            <span class="text-slate-600 font-mono">انضمام لاعب جديد</span>
        </div>

        <!-- بطاقة التسجيل -->
        <div class="w-full max-w-lg bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-3xl p-8 shadow-2xl space-y-6 relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>

            <!-- الترويسة -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl inline-flex items-center justify-center shadow-lg shadow-amber-500/20">
                    🏋️
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">فتح ملف لاعب جديد</h1>
                <p class="text-xs text-slate-400 leading-relaxed">
                    أدخل بياناتك الشخصية لتوليد كود العضوية والـ QR الخاص بالدخول
                </p>
            </div>

            <!-- النموذج -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- الاسم الكامل -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        الاسم الثلاثي
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        :class="[
                            'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition',
                            form.errors.name ? 'border-rose-500 text-rose-200' : 'border-slate-800 focus:border-amber-500'
                        ]"
                        placeholder="مثال: محمد أحمد الحلبي"
                    />
                    <p v-if="form.errors.name" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                        <span>⚠️</span>
                        <span>{{ form.errors.name }}</span>
                    </p>
                </div>

                <!-- شبكة لحقلي البريد ورقم الهاتف -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- البريد الإلكتروني -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            البريد الإلكتروني
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            :class="[
                                'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition',
                                form.errors.email ? 'border-rose-500 text-rose-200' : 'border-slate-800 focus:border-amber-500'
                            ]"
                            placeholder="name@example.com"
                        />
                        <p v-if="form.errors.email" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <span>⚠️</span>
                            <span>{{ form.errors.email }}</span>
                        </p>
                    </div>

                    <!-- رقم الهاتف -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            رقم الهاتف (للبحث ومطابقة شام كاش)
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            required
                            dir="ltr"
                            :class="[
                                'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition text-right',
                                form.errors.phone ? 'border-rose-500 text-rose-200' : 'border-slate-800 focus:border-amber-500'
                            ]"
                            placeholder="09xxxxxxxx"
                        />
                        <p v-if="form.errors.phone" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <span>⚠️</span>
                            <span>{{ form.errors.phone }}</span>
                        </p>
                    </div>
                </div>

                <!-- اختيار الجنس (تحديد فترة الدوام) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">
                        الجنس (لتحديد أوقات الصالة المتاحة لك)
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            :class="[
                                'flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold cursor-pointer transition select-none',
                                form.gender === 'male'
                                    ? 'border-amber-500 bg-amber-500/10 text-amber-400'
                                    : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700'
                            ]"
                        >
                            <input
                                type="radio"
                                v-model="form.gender"
                                value="male"
                                class="hidden"
                            />
                            <span>🚹</span>
                            <span>ذكر (دوام مسائي)</span>
                        </label>

                        <label
                            :class="[
                                'flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold cursor-pointer transition select-none',
                                form.gender === 'female'
                                    ? 'border-amber-500 bg-amber-500/10 text-amber-400'
                                    : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700'
                            ]"
                        >
                            <input
                                type="radio"
                                v-model="form.gender"
                                value="female"
                                class="hidden"
                            />
                            <span>🚺</span>
                            <span>أنثى (دوام صباحي)</span>
                        </label>
                    </div>
                    <p v-if="form.errors.gender" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                        <span>⚠️</span>
                        <span>{{ form.errors.gender }}</span>
                    </p>
                </div>

                <!-- شبكة لكلمة المرور والتأكيد -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- كلمة المرور -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">
                                كلمة المرور
                            </label>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="text-[10px] text-slate-400 hover:text-amber-400 transition"
                            >
                                {{ showPassword ? 'إخفاء' : 'إظهار' }}
                            </button>
                        </div>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            :class="[
                                'w-full px-4 py-3 bg-slate-950 border rounded-xl text-white text-sm focus:outline-none transition',
                                form.errors.password ? 'border-rose-500 text-rose-200' : 'border-slate-800 focus:border-amber-500'
                            ]"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <span>⚠️</span>
                            <span>{{ form.errors.password }}</span>
                        </p>
                    </div>

                    <!-- تأكيد كلمة المرور -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            تأكيد كلمة المرور
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none transition"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <!-- زر إنشاء الحساب -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl transition text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer mt-2"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ form.processing ? 'جاري فتح الحساب وإرسال الرمز...' : 'إنشاء حساب ومتابعة التحقق' }}</span>
                </button>
            </form>

            <!-- رابط تسجيل الدخول للمشتركين مسبقاً -->
            <div class="text-center pt-4 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    لديك حساب بالفعل في النادي؟
                    <Link href="/login" class="text-amber-400 hover:underline font-bold mr-1">
                        سجل دخولك هنا
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
