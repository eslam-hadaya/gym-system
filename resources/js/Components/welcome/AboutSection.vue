<script setup>
import { Landmark, Lightbulb } from "@lucide/vue";

const stats = [
    { value: "2021", label: "سنة التأسيس", highlight: false },
    { value: "+500", label: "لاعب مستمر", highlight: true },
    { value: "100%", label: "إدارة ذكية وموثقة", highlight: false },
];
</script>

<template>
    <section
        id="about"
        dir="rtl"
        class="border-b border-line-soft bg-surface-base px-4 py-16 sm:px-8 sm:py-24 lg:px-12 transition-colors duration-300"
    >
        <div class="mx-auto max-w-6xl">
            <div
                class="grid grid-cols-1 items-center gap-8 md:gap-10 lg:grid-cols-2 lg:gap-14"
            >
                <!-- ============ النص ============ -->
                <div class="space-y-5 sm:space-y-6">
                    <span
                        class="inline-block text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 sm:text-xs"
                    >
                        حكايتنا ورؤيتنا
                    </span>

                    <h2
                        class="text-2xl font-black leading-snug text-ink-primary sm:text-3xl lg:text-4xl"
                    >
                        تأسسنا عام 2021 بهدف واحد:
                        <br />
                        <span class="text-brand-600 dark:text-brand-400">
                            صناعة أبطال يغيّرون مقاييس اللياقة
                        </span>
                    </h2>

                    <p
                        class="text-sm leading-relaxed text-ink-secondary sm:text-base"
                    >
                        انطلق
                        <strong class="font-bold text-ink-primary"
                            >نادي الأبطال</strong
                        >
                        في صيف عام 2021 برؤية واضحة تهدف لإنهاء العشوائية في
                        التدريب الرياضي التقليدي. بدأنا كصالة تدريب متخصصة في
                        رفع الأثقال وكمال الأجسام، ثم تطورنا لنصبح أول منشأة
                        تجمع بين بيئة التدريب الجادة وأحدث تقنيات الإدارة
                        الرقمية السحابية.
                    </p>

                    <p
                        class="text-sm leading-relaxed text-ink-secondary sm:text-base"
                    >
                        اليوم، يضم النادي أكثر من 500 لاعب نشط ومئات الإنجازات
                        البدنية. حرصنا على توفير بيئة تحفزك على الحضور يومياً مع
                        احترام وقتك عبر أنظمة الدخول الذاتي والمدفوعات الشفافة.
                    </p>

                    <!-- الإحصائيات -->
                    <div
                        class="grid grid-cols-3 gap-3 border-t border-line-soft pt-5 sm:gap-4 sm:pt-6"
                    >
                        <div v-for="(stat, idx) in stats" :key="idx">
                            <span
                                class="block text-xl font-black sm:text-2xl lg:text-3xl"
                                :class="
                                    stat.highlight
                                        ? 'text-brand-600 dark:text-brand-400'
                                        : 'text-ink-primary'
                                "
                            >
                                {{ stat.value }}
                            </span>
                            <p
                                class="mt-1 text-[11px] text-ink-muted sm:text-xs"
                            >
                                {{ stat.label }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ============ بطاقة الفلسفة ============ -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-line-soft bg-surface-elevated p-6 sm:p-8 shadow-sm"
                >
                    <!-- توهج خفيف -->
                    <div
                        aria-hidden="true"
                        class="pointer-events-none absolute -top-24 -left-16 h-56 w-56 rounded-full bg-brand-400/10 blur-[80px]"
                    ></div>

                    <div class="relative space-y-4">
                        <span
                            class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-glass-bg text-brand-600 dark:text-brand-400"
                        >
                            <Landmark
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                :stroke-width="2"
                            />
                        </span>

                        <h3
                            class="text-lg font-bold text-ink-primary sm:text-xl"
                        >
                            إشراف كابتن وأبطال متخصصين
                        </h3>

                        <p
                            class="text-xs leading-relaxed text-ink-secondary sm:text-sm"
                        >
                            المنظومة الرياضية داخل النادي لا تعتمد على الأجهزة
                            وحدها، بل يديرها كادر من المدربين المعتمدين الذين
                            واكبوا النادي منذ انطلاقته، لتوفير برامج تدريبية
                            وتغذوية تحميك من الإصابات وتضمن لك التطور المستمر.
                        </p>

                        <!-- صندوق الفلسفة -->
                        <div
                            class="flex items-start gap-3 rounded-2xl border border-line-soft bg-glass-bg p-4 text-xs text-ink-secondary sm:text-sm"
                        >
                            <Lightbulb
                                class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400"
                                :stroke-width="2"
                            />
                            <p class="leading-relaxed">
                                <strong class="font-bold text-ink-primary"
                                    >فلسفتنا:</strong
                                >
                                ليس المهم كم ساعة تقضي في الصالة، بل كمية
                                التركيز والتكنيك الصحيح في كل تكرار.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
