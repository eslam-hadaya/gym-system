<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const plans = ref([
    { id: 1, name: 'باقة الشهر الواحد', days: 30, price: 150000, active_members: 142, is_active: true },
    { id: 2, name: 'باقة 3 أشهر (الأكثر طلباً)', days: 90, price: 400000, active_members: 228, is_active: true },
    { id: 3, name: 'الباقة السنوية VIP', days: 365, price: 1400000, active_members: 40, is_active: true },
]);

const showModal = ref(false);
const editingPlan = ref(null);
const form = ref({ name: '', days: 30, price: 0, is_active: true });

const openCreate = () => {
    editingPlan.value = null;
    form.value = { name: '', days: 30, price: 0, is_active: true };
    showModal.value = true;
};

const openEdit = (plan) => {
    editingPlan.value = plan;
    form.value = { ...plan };
    showModal.value = true;
};

const savePlan = () => {
    if (editingPlan.value) {
        Object.assign(editingPlan.value, form.value);
    } else {
        plans.value.push({
            id: Date.now(),
            ...form.value,
            active_members: 0,
        });
    }
    showModal.value = false;
};
</script>

<template>
    <Head title="إدارة الباقات والأسعار" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-white">باقات واشتراكات الصالة</h1>
                    <p class="text-xs text-slate-400 mt-1">تحديد مدة وصلاحية وتسعير الخطط وتتبع المشتركين النشطين في كل باقة</p>
                </div>
                <button
                    type="button"
                    @click="openCreate"
                    class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/20"
                >
                    <span>+</span>
                    <span>إضافة باقة جديدة</span>
                </button>
            </div>

            <!-- بطاقات الباقات الحالية -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 flex flex-col justify-between space-y-6 relative overflow-hidden group hover:border-slate-700 transition"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-amber-400">صلاحية: {{ plan.days }} يوم</span>
                            <span
                                :class="[
                                    'text-[10px] px-2 py-0.5 rounded-md font-bold',
                                    plan.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400'
                                ]"
                            >
                                {{ plan.is_active ? 'متاحة للتسجيل' : 'موقوفة' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-white">{{ plan.name }}</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">{{ plan.price.toLocaleString() }}</span>
                            <span class="text-xs text-slate-400">ل.س</span>
                        </div>
                        <p class="text-xs text-slate-400 pt-2 border-t border-slate-800">
                            👥 المشتركون الحاليون: <strong class="text-white">{{ plan.active_members }}</strong> لاعب
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="openEdit(plan)"
                            class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl transition border border-slate-700/80"
                        >
                            تعديل السعر والمدة
                        </button>
                    </div>
                </div>
            </div>

            <!-- نافذة التعديل والإضافة -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5">
                    <h3 class="text-base font-bold text-white">{{ editingPlan ? 'تعديل الباقة' : 'إضافة باقة جديدة' }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">اسم الباقة</label>
                            <input v-model="form.name" type="text" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">المدة بالأيام</label>
                                <input v-model.number="form.days" type="number" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white font-mono" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">السعر (ل.س)</label>
                                <input v-model.number="form.price" type="number" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white font-mono" />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs text-slate-400 hover:text-white">إلغاء</button>
                        <button type="button" @click="savePlan" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs">حفظ</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
