<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { Check, Crown } from "@lucide/vue";

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// ← منطق العرض: grid إذا ≤ 4، scroll أفقي إذا > 4
const isHorizontal = computed(() => props.plans.length > 4);

const getActionLink = (planId) => {
    if (!user.value) return `/register?plan_id=${planId}`;
    return user.value.role === "admin"
        ? "/admin/dashboard/plans"
        : `/member/dashboard/subscriptions?plan_id=${planId}`;
};

const getActionText = computed(() => {
    if (!user.value) return "اختر هذه الباقة وسجّل";
    return user.value.role === "admin"
        ? "تعديل الباقة من الإدارة"
        : "اشترك بهذه الباقة الآن";
});

const isPopular = (plan) => plan.days === 90;

const formatPrice = (price) =>
    Number(price).toLocaleString("ar-SY", { useGrouping: true });
</script>

<template>
    <section
        id="plans"
        dir="rtl"
        class="border-b border-line-soft bg-surface-base px-4 py-16 sm:px-8 sm:py-24 lg:px-12 transition-colors duration-300"
    >
        <div class="mx-auto max-w-6xl">
            <!-- رأس القسم -->
            <div class="mx-auto mb-10 max-w-2xl space-y-3 text-center sm:mb-14">
                <span
                    class="inline-block text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 sm:text-xs"
                >
                    خيارات الاشتراك المتاحة
                </span>
                <h2
                    class="text-2xl font-black leading-tight text-ink-primary sm:text-3xl lg:text-4xl"
                >
                    باقات شفافة تناسب أهدافك
                </h2>
                <p
                    class="text-sm leading-relaxed text-ink-secondary sm:text-base"
                >
                    اختر الباقة المناسبة وسجّل الآن، ويمكنك تسديد الاشتراك عند
                    الكابتن أو عبر شام كاش.
                </p>
            </div>

            <!-- حالة فارغة -->
            <div
                v-if="plans.length === 0"
                class="rounded-3xl border border-line-soft bg-surface-elevated p-8 text-center text-sm text-ink-secondary"
            >
                لا توجد باقات متاحة حالياً في النظام.
            </div>

            <!-- ============ Grid (≤ 4) ============ -->
            <div
                v-else-if="!isHorizontal"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4"
            >
                <PlanCard
                    v-for="plan in plans"
                    :key="plan.id"
                    :plan="plan"
                    :is-popular="isPopular(plan)"
                    :action-link="getActionLink(plan.id)"
                    :action-text="getActionText"
                />
            </div>

            <!-- ============ Horizontal Scroll (> 4) ============ -->
            <div
                v-else
                class="-mx-4 overflow-x-auto px-4 pb-4 snap-x snap-mandatory scroll-smooth sm:-mx-8 sm:px-8 lg:-mx-12 lg:px-12 [scrollbar-width:thin]"
                dir="ltr"
            >
                <div class="flex w-max gap-4 sm:gap-5">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="w-[280px] shrink-0 snap-start sm:w-[320px]"
                        dir="rtl"
                    >
                        <PlanCard
                            :plan="plan"
                            :is-popular="isPopular(plan)"
                            :action-link="getActionLink(plan.id)"
                            :action-text="getActionText"
                        />
                    </div>
                </div>
            </div>

            <!-- تلميح للتمرير -->
            <p
                v-if="isHorizontal"
                class="mt-4 text-center text-[11px] text-ink-muted sm:text-xs"
            >
                ← اسحب أفقياً لعرض المزيد من الباقات
            </p>
        </div>
    </section>
</template>
