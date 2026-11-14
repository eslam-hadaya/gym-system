<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Search,
    UserPlus,
    Filter,
    User,
} from "@lucide/vue";

const props = defineProps({
    members: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: "", status: "all" }) },
});

const searchQuery = ref(props.filters.search);
const statusFilter = ref(props.filters.status);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        "/admin/dashboard/members",
        {
            search: searchQuery.value || undefined,
            status:
                statusFilter.value === "all" ? undefined : statusFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(statusFilter, () => {
    applyFilters();
});
</script>

<template>
    <Head title="إدارة اللاعبين" />

    <AdminLayout>
        <div class="space-y-6" dir="rtl">
            <!-- الترويسة -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1 class="text-xl font-black text-ink-primary sm:text-2xl">
                        إدارة اللاعبين والأعضاء
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                        عرض وتعديل بيانات المشتركين وتتبع صلاحية العضويات
                    </p>
                </div>
              
            </div>

            <!-- البحث والتصفية -->
            <div
                class="flex flex-col gap-3 rounded-2xl border border-line-soft bg-surface-elevated p-4 sm:flex-row"
            >
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                        :stroke-width="2"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="ابحث بالاسم، برقم الهاتف، أو كود العضوية..."
                        class="w-full rounded-xl border border-line-strong bg-surface-base py-2.5 pl-4 pr-10 text-xs text-ink-primary placeholder:text-ink-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-300"
                    />
                </div>
                <div class="relative">
                    <Filter
                        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted"
                        :stroke-width="2"
                    />
                    <select
                        v-model="statusFilter"
                        class="w-full appearance-none rounded-xl border border-line-strong bg-surface-base py-2.5 pl-8 pr-10 text-xs text-ink-primary focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-300 sm:w-auto"
                    >
                        <option value="all">جميع الحالات</option>
                        <option value="active">اشتراك سارٍ</option>
                        <option value="expired">منتهي الصلاحية</option>
                        <option value="pending">غير مشترك</option>
                    </select>
                </div>
            </div>

            <!-- الجدول -->
            <div
                class="overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead
                            class="border-b border-line-soft bg-surface-soft font-semibold text-ink-secondary"
                        >
                            <tr>
                                <th class="px-3 py-3.5 sm:px-4">كود العضوية</th>
                                <th class="px-3 py-3.5 sm:px-4">اسم اللاعب</th>
                                <th class="px-3 py-3.5 sm:px-4">الهاتف</th>
                                <th class="px-3 py-3.5 sm:px-4">الفترة</th>
                                <th class="px-3 py-3.5 sm:px-4">الباقة</th>
                                <th class="px-3 py-3.5 sm:px-4">
                                    تاريخ الانتهاء
                                </th>
                                <th class="px-3 py-3.5 sm:px-4">الحالة</th>
                                <th class="px-3 py-3.5 text-center sm:px-4">
                                    إجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-line-soft text-ink-primary"
                        >
                            <tr
                                v-for="member in members.data"
                                :key="member.id"
                                class="transition-colors hover:bg-glass-bg-hover"
                            >
                                <td
                                    class="px-3 py-3.5 font-mono font-bold text-brand-600 dark:text-brand-400 sm:px-4"
                                >
                                    {{ member.code }}
                                </td>
                                <td class="px-3 py-3.5 sm:px-4">
                                    <div
                                        class="flex flex-wrap items-center gap-2 font-bold"
                                    >
                                        <span>{{ member.name }}</span>
                                        <span
                                            v-if="
                                                member.subscription_alert ===
                                                'expiring'
                                            "
                                            class="rounded-md border border-warning-500/20 bg-warning-500/10 px-2 py-0.5 text-[10px] font-bold text-warning-600 dark:text-warning-400"
                                            >قرب الانتهاء</span
                                        >
                                        <span
                                            v-else-if="
                                                member.subscription_alert ===
                                                'grace'
                                            "
                                            class="rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] font-bold text-danger-500"
                                            >مهلة السماح</span
                                        >
                                        <span
                                            v-else-if="
                                                member.subscription_alert ===
                                                'expired'
                                            "
                                            class="rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] font-bold text-danger-500"
                                            >منتهي</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-3 py-3.5 font-mono text-ink-secondary sm:px-4"
                                    dir="ltr"
                                >
                                    {{ member.phone }}
                                </td>
                                <td class="px-3 py-3.5 sm:px-4">
                                    <span
                                        class="inline-flex items-center gap-1 text-[11px]"
                                    >
                                        <span
                                            v-if="member.gender === 'ذكر'"
                                            class="text-brand-500"
                                            ><User
                                                class="h-3.5 w-3.5"
                                                :stroke-width="2.5"
                                        /></span>
                                        <span v-else class="text-brand-500"
                                            ><User
                                                class="h-3.5 w-3.5"
                                                :stroke-width="2.5"
                                        /></span>
                                        {{
                                            member.gender === "ذكر"
                                                ? "مسائي"
                                                : "صباحي"
                                        }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 sm:px-4">
                                    {{ member.plan }}
                                </td>
                                <td
                                    class="px-3 py-3.5 font-mono text-ink-secondary sm:px-4"
                                >
                                    {{ member.expiry }}
                                </td>
                                <td class="px-3 py-3.5 sm:px-4">
                                    <span
                                        v-if="member.status === 'active'"
                                        class="rounded-md border border-success-500/20 bg-success-500/10 px-2 py-0.5 text-[10px] font-bold text-success-600 dark:text-success-400"
                                        >سارٍ</span
                                    >
                                    <span
                                        v-else-if="member.status === 'expired'"
                                        class="rounded-md border border-danger-500/20 bg-danger-500/10 px-2 py-0.5 text-[10px] font-bold text-danger-500"
                                        >منتهٍ</span
                                    >
                                    <span
                                        v-else
                                        class="rounded-md bg-glass-bg px-2 py-0.5 text-[10px] font-bold text-ink-muted"
                                        >معلق</span
                                    >
                                </td>
                                <td class="px-3 py-3.5 text-center sm:px-4">
                                    <Link
                                        :href="`/admin/dashboard/finance?member=${member.code}`"
                                        class="inline-block rounded-lg border border-line-soft bg-surface-base px-2.5 py-1 text-[10px] font-bold text-ink-secondary transition hover:border-brand-500/30 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400"
                                    >
                                        سجل الاشتراكات
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="members.data.length === 0">
                                <td
                                    colspan="8"
                                    class="py-8 text-center text-xs text-ink-muted"
                                >
                                    لا يوجد لاعب يطابق معايير البحث.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- الترقيم -->
                <div
                    v-if="members.links && members.links.length > 3"
                    class="flex justify-center gap-1 border-t border-line-soft p-4"
                >
                    <Link
                        v-for="(link, index) in members.links"
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
    </AdminLayout>
</template>
