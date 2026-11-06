<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    members: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all' }),
    },
});

const searchQuery = ref(props.filters.search);
const statusFilter = ref(props.filters.status);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        '/admin/dashboard/members',
        {
            search: searchQuery.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
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
        <div class="space-y-6">
            <!-- الترويسة وأزرار التحكم -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white">إدارة اللاعبين والأعضاء</h1>
                    <p class="text-xs text-slate-400 mt-1">عرض وتعديل بيانات المشتركين وتتبع صلاحية العضويات</p>
                </div>
                <Link
                    href="/admin/dashboard/members/create"
                    class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/20"
                >
                    <span>+</span>
                    <span>إضافة لاعب يدوي</span>
                </Link>
            </div>

            <!-- شريط البحث والتصفية -->
            <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="ابحث بالاسم، برقم الهاتف (09xxxxxxxx)، أو كود العضوية..."
                        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none transition"
                    />
                </div>
                <select
                    v-model="statusFilter"
                    class="px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:border-amber-500 focus:outline-none transition"
                >
                    <option value="all">جميع الحالات</option>
                    <option value="active">اشتراك سارٍ</option>
                    <option value="expired">منتهي الصلاحية</option>
                    <option value="pending">غير مشترك</option>
                </select>
            </div>

            <!-- جدول اللاعبين -->
            <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3.5 px-4">كود العضوية</th>
                                <th class="py-3.5 px-4">اسم اللاعب</th>
                                <th class="py-3.5 px-4">رقم الهاتف</th>
                                <th class="py-3.5 px-4">الجنس / الفترة</th>
                                <th class="py-3.5 px-4">الباقة الحالية</th>
                                <th class="py-3.5 px-4">تاريخ الانتهاء</th>
                                <th class="py-3.5 px-4">الحالة</th>
                                <th class="py-3.5 px-4 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-for="member in members.data" :key="member.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-400">{{ member.code }}</td>
                                <td class="py-3.5 px-4 font-bold text-white">{{ member.name }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-400" dir="ltr">{{ member.phone }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 text-[11px]">
                                        {{ member.gender === 'ذكر' ? '🚹 مسائي' : '🚺 صباحي' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">{{ member.plan }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-400">{{ member.expiry }}</td>
                                <td class="py-3.5 px-4">
                                    <span
                                        v-if="member.status === 'active'"
                                        class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold"
                                    >
                                        سارٍ
                                    </span>
                                    <span
                                        v-else-if="member.status === 'expired'"
                                        class="px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold"
                                    >
                                        منتهٍ
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400 text-[10px] font-bold"
                                    >
                                        معلق
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <Link
                                        :href="`/admin/dashboard/finance?member=${member.code}`"
                                        class="px-2.5 py-1 bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 rounded-lg text-[10px] font-bold transition inline-block"
                                    >
                                        سجل الاشتراكات
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="members.data.length === 0">
                                <td colspan="8" class="py-8 text-center text-slate-500 text-xs">
                                    لا يوجد لاعب يطابق معايير البحث.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- الترقيم (Pagination) -->
                <div v-if="members.links && members.links.length > 3" class="p-4 border-t border-slate-800 flex justify-center gap-1">
                    <Link
                        v-for="(link, index) in members.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 text-xs rounded-lg border transition"
                        :class="link.active ? 'bg-amber-500 text-slate-950 border-amber-500 font-bold' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                        :disabled="!link.url"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
