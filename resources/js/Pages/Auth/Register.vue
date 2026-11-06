<script setup>
import { useForm, Link } from '@inertiajs/vue3';

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
    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-8 relative overflow-hidden" dir="rtl">
        <div class="w-full max-w-lg space-y-6 bg-slate-900/60 p-8 rounded-3xl border border-slate-800 backdrop-blur-xl shadow-2xl relative z-10">
            <div class="text-center space-y-2">
                <Link href="/" class="inline-flex items-center gap-2 text-amber-500 font-black text-2xl tracking-wider">
                    <span>🏋️</span>
                    <span>نادي الأبطال</span>
                </Link>
                <h2 class="text-xl font-bold text-white tracking-tight">إنشاء حساب مشترك جديد</h2>
                <p class="text-xs text-slate-400">انضم للنادي واحصل على كود عضويتك وبطاقتك الرقمية فوراً</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">الاسم الثلاثي</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none"
                        :class="{ 'border-rose-500': form.errors.name }"
                        placeholder="محمد أحمد علي"
                    />
                    <p v-if="form.errors.name" class="text-[11px] text-rose-400 font-medium">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">البريد الإلكتروني</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none"
                            :class="{ 'border-rose-500': form.errors.email }"
                            placeholder="name@example.com"
                        />
                        <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-medium">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">رقم الهاتف (سوري)</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none font-mono"
                            :class="{ 'border-rose-500': form.errors.phone }"
                            placeholder="09xxxxxxxx"
                        />
                        <p v-if="form.errors.phone" class="text-[11px] text-rose-400 font-medium">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">فترة التدريب (الجنس)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition select-none text-xs font-bold"
                            :class="form.gender === 'male' ? 'bg-amber-500/10 border-amber-500 text-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                        >
                            <input type="radio" v-model="form.gender" value="male" class="hidden" />
                            <span>فترة الرجال (مسائي)</span>
                        </label>
                        <label
                            class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition select-none text-xs font-bold"
                            :class="form.gender === 'female' ? 'bg-amber-500/10 border-amber-500 text-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                        >
                            <input type="radio" v-model="form.gender" value="female" class="hidden" />
                            <span>فترة السيدات (صباحي)</span>
                        </label>
                    </div>
                    <p v-if="form.errors.gender" class="text-[11px] text-rose-400 font-medium">{{ form.errors.gender }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">كلمة المرور</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none font-mono"
                            :class="{ 'border-rose-500': form.errors.password }"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-medium">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">تأكيد كلمة المرور</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition outline-none font-mono"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl text-sm transition shadow-lg shadow-amber-500/10 cursor-pointer flex justify-center items-center gap-2 mt-4"
                >
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>إنشاء الحساب ومتابعة التحقق</span>
                </button>

                <p class="text-center text-xs text-slate-400 pt-2">
                    لديك حساب بالفعل؟
                    <Link href="/login" class="text-amber-400 font-semibold hover:underline">سجل الدخول هنا</Link>
                </p>
            </form>
        </div>
    </div>
</template>
