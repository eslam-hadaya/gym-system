<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative overflow-hidden py-24 sm:py-32 px-6 sm:px-12 text-center border-b border-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-slate-950 to-slate-950 pointer-events-none"></div>

        <div class="relative max-w-4xl mx-auto space-y-8">
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-400 text-xs font-semibold tracking-wide">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                بيئة رياضية بمعايير عالمية ونظام رقمي ذكي
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight sm:leading-tight">
                اصنع نسختك الأقوى <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-l from-amber-300 via-amber-500 to-amber-600">
                    واحترف انضباطك الرياضي
                </span>
            </h1>

            <p class="text-base sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                نادي الأبطال يجمع لك بين نخبة المعدات التدريبية والإدارة الرقمية الكاملة؛ سجّل معنا، تتبّع اشتراكك، وادخل الصالة عبر هاتفك مباشرة.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
                <Link
                    href="/register"
                    class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-2xl transition text-base shadow-xl shadow-amber-500/25 hover:scale-[1.02] transform"
                >
                    سجّل اشتراكك الآن
                </Link>
                <a
                    href="#about"
                    class="px-8 py-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white font-semibold rounded-2xl transition text-base"
                >
                    اكتشف قصة تأسيسنا ↓
                </a>
            </div>
        </div>
    </section>
</template>
