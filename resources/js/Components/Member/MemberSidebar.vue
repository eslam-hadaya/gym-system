<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user || { name: 'اللاعب' });
const member = computed(() => page.props.member || null);

const currentUrl = computed(() => page.url);
const isActive = (url) => {
    if (url === '/member/dashboard') {
        return currentUrl.value === '/member/dashboard';
    }
    return currentUrl.value.startsWith(url);
};

const navItems = [
    {
        name: 'بطاقتي الرقمية (QR)',
        url: '/member/dashboard',
        icon: '📱',
        badge: 'الكود',
    },
    {
        name: 'اشتراكي والتجديد',
        url: '/member/dashboard/subscriptions',
        icon: '💳',
        badge: null,
    },
    {
        name: 'سجل حركات الحضور',
        url: '/member/dashboard/history',
        icon: '🕒',
        badge: null,
    },
];
</script>

<template>
    <!-- سايدبار مثبت على جهة اليمين في بيئة RTL -->
    <aside class="w-64 bg-slate-900/95 border-l border-slate-800 flex flex-col justify-between shrink-0 h-screen sticky top-0 z-40 select-none" dir="rtl">
        <div>
            <!-- ترويسة السايدبار والشعار -->
            <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800/80">
                <span class="w-10 h-10 rounded-2xl bg-amber-500 text-slate-950 font-black text-xl flex items-center justify-center shadow-lg shadow-amber-500/20">
                    🏋️
                </span>
                <div class="overflow-hidden">
                    <h2 class="text-sm font-black text-white tracking-wide truncate">نادي الأبطال</h2>
                    <span class="text-[10px] text-amber-400/90 font-mono font-medium block">
                        بوابة اللاعب الرياضي
                    </span>
                </div>
            </div>

            <!-- قائمة التبويبات -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <div class="text-[10px] font-bold text-slate-500 px-3 py-2 uppercase tracking-wider">
                    حسابي والخدمات
                </div>

                <Link
                    v-for="item in navItems"
                    :key="item.url"
                    :href="item.url"
                    :class="[
                        'flex items-center justify-between px-3.5 py-3 rounded-xl transition duration-150 group',
                        isActive(item.url)
                            ? 'bg-amber-500 text-slate-950 font-black shadow-md shadow-amber-500/15'
                            : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-base group-hover:scale-110 transition-transform">
                            {{ item.icon }}
                        </span>
                        <span>{{ item.name }}</span>
                    </div>

                    <span
                        v-if="item.badge"
                        :class="[
                            'text-[10px] px-2 py-0.5 rounded-md font-mono font-bold',
                            isActive(item.url)
                                ? 'bg-slate-950/20 text-slate-950'
                                : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                        ]"
                    >
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>
        </div>

        <!-- معلومات اللاعب وزر تسجيل الخروج -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/30">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700/60 text-amber-400 font-bold flex items-center justify-center text-xs shrink-0">
                        🏃
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-amber-400 font-mono font-medium truncate">
                            {{ member?.member_code || 'مشترك رياضي' }}
                        </p>
                    </div>
                </div>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition border border-transparent hover:border-rose-500/20 shrink-0 cursor-pointer"
                    title="تسجيل الخروج"
                >
                    🚪
                </Link>
            </div>
        </div>
    </aside>
</template>
