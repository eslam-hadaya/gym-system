<script setup>
import { QrCode, Dumbbell, CreditCard, Target } from "@lucide/vue";

const features = [
    {
        title: "دخول سريع بنظام QR الذكي",
        description:
            "بطاقة رقمية على هاتفك تُمسح في ثانية واحدة على الباب دون الحاجة لبطاقات ورقية أو انتظار الموظف.",
        icon: QrCode,
    },
    {
        title: "تجهيزات ومعدات دولية",
        description:
            "أحدث آلات الوزن الحر والأجهزة الهيدروليكية المعتمدة لعزل العضلات بأمان وكفاءة تامة.",
        icon: Dumbbell,
    },
    {
        title: "طرق دفع محلية مرنة",
        description:
            "إمكانية تجديد اشتراكك مباشرة من حسابك عبر شام كاش (Sham Cash) برقم المعاملة أو كاش في الصندوق.",
        icon: CreditCard,
    },
    {
        title: "إشراف كباتن متخصصين",
        description:
            "كادر تدريبي متفرغ لمتابعة خططك التدريبية، تصحيح الأداء الحركي، وتقديم التوجيه الغذائي السليم.",
        icon: Target,
    },
];
</script>

<template>
    <section
        id="features"
        dir="rtl"
        class="border-b border-line-soft bg-surface-base px-4 py-16 sm:px-8 sm:py-24 lg:px-12 transition-colors duration-300"
    >
        <div class="mx-auto max-w-6xl">
            <!-- رأس القسم -->
            <div class="mx-auto mb-10 max-w-2xl space-y-3 text-center sm:mb-14">
                <span
                    class="inline-block text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 sm:text-xs"
                >
                    لماذا نادي الأبطال؟
                </span>
                <h2
                    class="text-2xl font-black leading-tight text-ink-primary sm:text-3xl lg:text-4xl"
                >
                    كل ما يحتاجه الرياضي الحقيقي
                </h2>
                <p
                    class="text-sm leading-relaxed text-ink-secondary sm:text-base"
                >
                    صممنا تفاصيل الصالة والنظام الرقمي لتجعل تجربتك خالية من أي
                    تعطيل أو هدر للوقت.
                </p>
            </div>

            <!-- شبكة البطاقات -->
            <div
                class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2 lg:grid-cols-4"
            >
                <article
                    v-for="(feature, idx) in features"
                    :key="idx"
                    class="group rounded-2xl border border-line-soft bg-surface-elevated p-5 sm:p-6 transition-colors duration-200 hover:border-brand-500/40 hover:bg-surface-soft"
                >
                    <span
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-glass-bg text-brand-600 transition-colors duration-200 group-hover:bg-brand-500 group-hover:text-white dark:text-brand-400 dark:group-hover:text-white"
                    >
                        <component
                            :is="feature.icon"
                            class="h-5 w-5 sm:h-6 sm:w-6"
                            :stroke-width="2"
                        />
                    </span>

                    <h3
                        class="mb-1.5 text-sm font-bold text-ink-primary sm:text-base"
                    >
                        {{ feature.title }}
                    </h3>
                    <p
                        class="text-xs leading-relaxed text-ink-secondary sm:text-sm"
                    >
                        {{ feature.description }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
