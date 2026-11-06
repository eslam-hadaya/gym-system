<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const dashboardUrl = computed(() => {
    if (!user.value) return '/login';
    return user.value.role === 'admin' ? '/admin/dashboard' : '/member/dashboard';
});
</script>

<template>
    <header class="h-20 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50 px-6 sm:px-12 flex items-center justify-between select-none" dir="rtl">
        <!-- الشعار -->
        <Link href="/" class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-950 font-black text-2xl flex items-center justify-center shadow-lg shadow-amber-500/20">
                🏋️
            </span>
            <div>
                <span class="text-lg font-black tracking-tight text-white block">نادي الأبطال</span>
                <span class="text-[10px] text-amber-400 font-semibold tracking-wider uppercase block font-mono">Champions Gym Club</span>
            </div>
        </Link>

        <!-- روابط الأقسام السريعة -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-400">
            <a href="#about" class="hover:text-amber-400 transition">من نحن وتأسيسنا</a>
            <a href="#features" class="hover:text-amber-400 transition">مميزات الصالة</a>
            <a href="#plans" class="hover:text-amber-400 transition">الباقات والاشتراكات</a>
            <a href="#contact" class="hover:text-amber-400 transition">تواصل معنا</a>
        </nav>

        <!-- منطقة الأزرار: تتبدل تلقائياً وفق حالة الجلسة -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- حالة المستخدم مسجل الدخول بالفعل -->
            <template v-if="user">
                <Link
                    :href="dashboardUrl"
                    class="text-xs sm:text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 px-4 sm:px-5 py-2.5 rounded-xl transition shadow-lg shadow-amber-500/20 flex items-center gap-2"
                >
                    <span>{{ user.role === 'admin' ? '📊 لوحة الإدارة' : '🪪 بطاقتي والداشبورد' }}</span>
                </Link>


            </template>

            <!-- حالة الزائر غير المسجل (Guest) -->
            <template v-else>
                <Link
                    href="/login"
                    class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition px-4 py-2 rounded-xl hover:bg-slate-900 border border-slate-800 hover:border-slate-700"
                >
                    تسجيل الدخول
                </Link>

                <Link
                    href="/register"
                    class="text-xs sm:text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 px-4 sm:px-5 py-2.5 rounded-xl transition shadow-lg shadow-amber-500/20"
                >
                    انضم إلينا
                </Link>
            </template>
        </div>
    </header>
</template>
