<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { ArrowDown } from "@lucide/vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const targetUrl = computed(() => {
    if (!user.value) return "/register";
    return user.value.role === "admin"
        ? "/admin/dashboard"
        : "/member/dashboard";
});

const actionLabel = computed(() => {
    if (!user.value) return "سجّل اشتراكك الآن";
    return user.value.role === "admin"
        ? "لوحة تحكم الإدارة"
        : "بطاقة دخولي (QR)";
});
</script>

<template>
    <section
        dir="rtl"
        class="relative overflow-hidden border-b border-line-soft bg-surface-base py-14 sm:py-20 lg:py-28 px-4 sm:px-8 lg:px-12 text-center transition-colors duration-300"
    >
        <!-- إضاءة ناعمة -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-32 left-1/2 h-[420px] w-[420px] -translate-x-1/2 rounded-full bg-brand-400/15 blur-[120px]"
            ></div>
            <div
                class="absolute top-1/3 -left-24 h-72 w-72 rounded-full bg-brand-300/15 blur-[100px]"
            ></div>
        </div>

        <div class="relative mx-auto max-w-4xl space-y-6 sm:space-y-8">
            <!-- Badge -->
            <div
                class="inline-flex items-center gap-2 rounded-full border border-glass-border bg-glass-bg px-3 py-1.5 sm:px-4 sm:py-2 text-[11px] sm:text-xs font-semibold tracking-wide text-brand-600 dark:text-brand-300 shadow-sm backdrop-blur-glass"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                بيئة رياضية بمعايير عالمية ونظام رقمي ذكي
            </div>

            <!-- Heading -->
            <h1
                class="text-3xl sm:text-5xl lg:text-6xl font-black text-ink-primary leading-[1.25] sm:leading-[1.2] tracking-normal"
            >
                اصنع نسختك الأقوى
                <br class="hidden sm:block" />
                <span
                    class="inline-block mt-2 sm:mt-3 text-brand-600 dark:text-brand-400"
                >
                    واحترف انضباطك الرياضي
                </span>
            </h1>

            <!-- Subtext -->
            <p
                class="mx-auto max-w-2xl text-sm sm:text-lg leading-relaxed text-ink-secondary"
            >
                نادي الأبطال يجمع لك بين نخبة المعدات التدريبية والإدارة الرقمية
                الكاملة؛ سجّل معنا، تتبّع اشتراكك، وادخل الصالة عبر هاتفك مباشرة.
            </p>

            <!-- CTAs -->
            <div
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 pt-2 sm:pt-4"
            >
                <Link
                    :href="targetUrl"
                    class="inline-flex items-center justify-center rounded-2xl px-6 py-3.5 sm:px-8 sm:py-4 text-sm sm:text-base font-bold bg-brand-500 text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base active:bg-brand-600"
                >
                    {{ actionLabel }}
                </Link>

                <a
                    href="#about"
                    class="inline-flex items-center justify-center gap-1.5 rounded-2xl px-6 py-3.5 sm:px-8 sm:py-4 text-sm sm:text-base font-semibold border border-line-strong bg-glass-bg backdrop-blur-glass text-ink-secondary transition-colors duration-200 hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base"
                >
                    اكتشف قصة تأسيسنا
                    <ArrowDown class="h-4 w-4" :stroke-width="2" />
                </a>
            </div>
        </div>
    </section>
</template>
