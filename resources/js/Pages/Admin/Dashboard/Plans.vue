<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
});

const showModal = ref(false);
const editingPlan = ref(null);

const form = useForm({
    name: '',
    days: 30,
    price: 0,
    is_active: true,
});

const openCreate = () => {
    editingPlan.value = null;
    form.reset();
    form.clearErrors();
    form.days = 30;
    form.price = 150000;
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (plan) => {
    editingPlan.value = plan;
    form.clearErrors();
    form.name = plan.name;
    form.days = plan.days;
    form.price = plan.price;
    form.is_active = Boolean(plan.is_active);
    showModal.value = true;
};

const savePlan = () => {
    if (editingPlan.value) {
        form.put(`/admin/dashboard/plans/${editingPlan.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post('/admin/dashboard/plans', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};
</script>

<template>
    <Head title="إدارة الباقات والأسعار" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white">باقات واشتراكات الصالة</h1>
                    <p class="text-xs text-slate-400 mt-1">تحديد مدة وصلاحية وتسعير الخطط وتتبع المشتركين النشطين في كل باقة</p>
                </div>
                <button
                    type="button"
                    @click="openCreate"
                    class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/20 cursor-pointer"
                >
                    <span>+</span>
                    <span>إضافة باقة جديدة</span>
                </button>
            </div>

            <!-- بطاقات الباقات الحقيقية المجلوبة من السيرفر -->
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
                                    'text-[10px] px-2 py-0.5 rounded-md font-bold border',
                                    plan.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800 text-slate-400 border-slate-700'
                                ]"
                            >
                                {{ plan.is_active ? 'متاحة للتسجيل' : 'موقوفة' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-white">{{ plan.name }}</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white font-mono">{{ plan.price.toLocaleString() }}</span>
                            <span class="text-xs text-slate-400">ل.س</span>
                        </div>
                        <p class="text-xs text-slate-400 pt-2 border-t border-slate-800">
                            👥 المشتركون الحاليون: <strong class="text-white font-mono">{{ plan.active_members }}</strong> لاعب
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="openEdit(plan)"
                            class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl transition border border-slate-700/80 cursor-pointer"
                        >
                            تعديل السعر والمدة
                        </button>
                    </div>
                </div>

                <div v-if="plans.length === 0" class="col-span-3 py-12 text-center text-slate-500 text-xs">
                    لا توجد أي باقات مدخلة في النظام حتى الآن.
                </div>
            </div>

            <!-- نافذة التعديل والإضافة (Modal) -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5">
                    <h3 class="text-base font-bold text-white">{{ editingPlan ? 'تعديل الباقة' : 'إضافة باقة جديدة' }}</h3>

                    <form @submit.prevent="savePlan" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">اسم الباقة</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-white outline-none transition"
                                :class="{ 'border-rose-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-[11px] text-rose-400 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">المدة بالأيام</label>
                                <input
                                    v-model.number="form.days"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-white font-mono outline-none transition"
                                    :class="{ 'border-rose-500': form.errors.days }"
                                />
                                <p v-if="form.errors.days" class="text-[11px] text-rose-400 mt-1">{{ form.errors.days }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">السعر (ل.س)</label>
                                <input
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    required
                                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-white font-mono outline-none transition"
                                    :class="{ 'border-rose-500': form.errors.price }"
                                />
                                <p v-if="form.errors.price" class="text-[11px] text-rose-400 mt-1">{{ form.errors.price }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 focus:ring-offset-0 cursor-pointer"
                            />
                            <label for="is_active" class="text-xs text-slate-300 select-none cursor-pointer">
                                إتاحة هذه الباقة للتسجيل والاشتراك
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-800">
                            <button
                                type="button"
                                @click="showModal = false"
                                class="px-4 py-2 text-xs text-slate-400 hover:text-white transition cursor-pointer"
                            >
                                إلغاء
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer"
                            >
                                {{ form.processing ? 'جاري الحفظ...' : 'حفظ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
