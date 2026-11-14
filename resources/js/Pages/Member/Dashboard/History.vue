<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MemberLayout from "@/Layouts/MemberLayout.vue";

defineProps({
    member: {
        type: Object,
        required: true,
    },
    checkIns: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({ total_checkins: 0, this_month: 0 }),
    },
});
</script>

<template>
    <Head title="سجل حركات الحضور" />

    <MemberLayout>
        <div class="space-y-6" dir="rtl">
            <!-- ============ الرأس ============ -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1 class="text-2xl font-black text-ink-primary">
                        سجل الحركات والتمارين
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary">
                        تتبع التزامك الرياضي وحركات دخول الصالة المسجلة بالـ QR
                    </p>
                </div>
            </div>

            <!-- ============ إحصائيات الالتزام ============ -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    class="rounded-2xl border border-line-soft bg-surface-elevated p-5 space-y-1"
                >
                    <span class="text-xs text-ink-secondary"
                        >إجمالي مرات التمرين المسجلة</span
                    >
                    <p
                        class="font-mono text-2xl font-black text-brand-600 dark:text-brand-400"
                    >
                        {{ stats.total_checkins }}
                        <span class="font-sans text-xs text-ink-muted"
                            >تمرين</span
                        >
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-line-soft bg-surface-elevated p-5 space-y-1"
                >
                    <span class="text-xs text-ink-secondary"
                        >حضور هذا الشهر</span
                    >
                    <p
                        class="font-mono text-2xl font-black text-success-600 dark:text-success-400"
                    >
                        {{ stats.this_month }}
                        <span class="font-sans text-xs text-ink-muted"
                            >أيام</span
                        >
                    </p>
                </div>
            </div>

            <!-- ============ جدول الحركات ============ -->
            <div
                class="overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead
                            class="border-b border-line-soft bg-surface-soft font-semibold text-ink-secondary"
                        >
                            <tr>
                                <th class="px-4 py-3.5">التاريخ</th>
                                <th class="px-4 py-3.5">توقيت الدخول</th>
                                <th class="px-4 py-3.5">الحالة</th>
                                <th class="px-4 py-3.5">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-line-soft text-ink-primary"
                        >
                            <tr v-if="checkIns.data.length === 0">
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-xs text-ink-muted"
                                >
                                    لا توجد حركات حضور مسجلة حتى الآن.
                                </td>
                            </tr>
                            <tr
                                v-for="entry in checkIns.data"
                                :key="entry.id"
                                class="transition-colors hover:bg-glass-bg-hover"
                            >
                                <td class="px-4 py-3.5 font-mono font-bold">
                                    {{ entry.date }}
                                </td>
                                <td
                                    class="px-4 py-3.5 font-mono text-brand-600 dark:text-brand-400"
                                >
                                    {{ entry.time }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        v-if="entry.status === 'valid'"
                                        class="rounded-md border border-success-500/20 bg-success-500/10 px-2 py-0.5 text-[10px] font-bold text-success-600 dark:text-success-400"
                                    >
                                        دخول ناجح
                                    </span>
                                    <span
                                        v-else
                                        class="rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] font-bold text-danger-500"
                                    >
                                        مرفوض / منتهي
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-ink-secondary">
                                    {{ entry.note }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ============ الترقيم ============ -->
                <div
                    v-if="checkIns.links && checkIns.links.length > 3"
                    class="flex justify-center gap-1 border-t border-line-soft p-4"
                >
                    <Link
                        v-for="(link, index) in checkIns.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="rounded-lg border px-3 py-1 text-xs transition-colors"
                        :class="
                            link.active
                                ? 'border-brand-500 bg-brand-500 font-bold text-white'
                                : 'border-line-strong bg-surface-base text-ink-secondary hover:text-ink-primary'
                        "
                        :disabled="!link.url"
                    />
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
