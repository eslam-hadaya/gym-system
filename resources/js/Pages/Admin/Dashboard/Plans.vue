<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Plus, Users, X, Edit2, CheckCircle2, AlertCircle } from "@lucide/vue";

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
});

const showModal = ref(false);
const editingPlan = ref(null);

const form = useForm({
    name: "",
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
        form.post("/admin/dashboard/plans", {
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
        <div class="space-y-6" dir="rtl">
            <!-- الترويسة -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1 class="text-xl font-black text-ink-primary sm:text-2xl">
                        باقات واشتراكات الصالة
                    </h1>
                    <p class="mt-1 text-xs text-ink-secondary sm:text-sm">
                        تحديد مدة وصلاحية وتسعير الخطط وتتبع المشتركين النشطين
                        في كل باقة
                    </p>
                </div>
                <button
                    type="button"
                    @click="openCreate"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-400"
                >
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    <span>إضافة باقة جديدة</span>
                </button>
            </div>

            <!-- بطاقات الباقات -->
            <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="relative flex flex-col justify-between space-y-5 overflow-hidden rounded-2xl border border-line-soft bg-surface-elevated p-5 shadow-sm transition-all hover:border-brand-500/30 sm:space-y-6 sm:p-6"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400"
                            >
                                صلاحية: {{ plan.days }} يوم
                            </span>
                            <span
                                class="rounded-md border px-2 py-0.5 text-[10px] font-bold"
                                :class="
                                    plan.is_active
                                        ? 'border-success-500/20 bg-success-500/10 text-success-600 dark:text-success-400'
                                        : 'border-line-strong bg-glass-bg text-ink-muted'
                                "
                            >
                                {{
                                    plan.is_active ? "متاحة للتسجيل" : "موقوفة"
                                }}
                            </span>
                        </div>

                        <h3 class="text-lg font-black text-ink-primary">
                            {{ plan.name }}
                        </h3>

                        <div class="flex items-baseline gap-1">
                            <span
                                class="font-mono text-2xl font-black text-ink-primary sm:text-3xl"
                            >
                                {{ plan.price.toLocaleString() }}
                            </span>
                            <span class="text-xs text-ink-muted">ل.س</span>
                        </div>

                        <div
                            class="flex items-center gap-1.5 border-t border-line-soft pt-3 text-xs text-ink-secondary"
                        >
                            <Users
                                class="h-3.5 w-3.5 text-brand-500"
                                :stroke-width="2"
                            />
                            <span>المشتركون الحاليون:</span>
                            <strong
                                class="font-mono font-bold text-ink-primary"
                                >{{ plan.active_members }}</strong
                            >
                            <span>لاعب</span>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openEdit(plan)"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-line-strong bg-surface-base py-2 text-xs font-semibold text-ink-secondary transition hover:border-brand-500/30 hover:bg-brand-500/5 hover:text-brand-600 dark:hover:text-brand-400"
                    >
                        <Edit2 class="h-3.5 w-3.5" :stroke-width="2" />
                        <span>تعديل السعر والمدة</span>
                    </button>
                </div>

                <div
                    v-if="plans.length === 0"
                    class="col-span-full py-12 text-center text-xs text-ink-muted"
                >
                    لا توجد أي باقات مدخلة في النظام حتى الآن.
                </div>
            </div>

            <!-- Modal (نافذة التعديل والإضافة) -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="showModal = false"
                ></div>

                <div
                    class="relative w-full max-w-md space-y-5 rounded-3xl border border-line-soft bg-surface-elevated p-5 shadow-2xl sm:p-6"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-ink-primary">
                            {{
                                editingPlan
                                    ? "تعديل الباقة"
                                    : "إضافة باقة جديدة"
                            }}
                        </h3>
                        <button
                            type="button"
                            @click="showModal = false"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-muted transition hover:bg-glass-bg-hover hover:text-ink-primary"
                        >
                            <X class="h-4 w-4" :stroke-width="2" />
                        </button>
                    </div>

                    <form @submit.prevent="savePlan" class="space-y-4">
                        <div>
                            <label
                                for="plan-name"
                                class="mb-1 block text-xs font-medium text-ink-secondary"
                                >اسم الباقة</label
                            >
                            <input
                                id="plan-name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-xl border bg-surface-base px-3 py-2.5 text-xs text-ink-primary outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-300"
                                :class="{
                                    'border-danger-500': form.errors.name,
                                    'border-line-strong': !form.errors.name,
                                }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-[11px] font-medium text-danger-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    for="plan-days"
                                    class="mb-1 block text-xs font-medium text-ink-secondary"
                                    >المدة بالأيام</label
                                >
                                <input
                                    id="plan-days"
                                    v-model.number="form.days"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full rounded-xl border bg-surface-base px-3 py-2.5 font-mono text-xs text-ink-primary outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-300"
                                    :class="{
                                        'border-danger-500': form.errors.days,
                                        'border-line-strong': !form.errors.days,
                                    }"
                                />
                                <p
                                    v-if="form.errors.days"
                                    class="mt-1 text-[11px] font-medium text-danger-500"
                                >
                                    {{ form.errors.days }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="plan-price"
                                    class="mb-1 block text-xs font-medium text-ink-secondary"
                                    >السعر (ل.س)</label
                                >
                                <input
                                    id="plan-price"
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    required
                                    class="w-full rounded-xl border bg-surface-base px-3 py-2.5 font-mono text-xs text-ink-primary outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-300"
                                    :class="{
                                        'border-danger-500': form.errors.price,
                                        'border-line-strong':
                                            !form.errors.price,
                                    }"
                                />
                                <p
                                    v-if="form.errors.price"
                                    class="mt-1 text-[11px] font-medium text-danger-500"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 cursor-pointer rounded border-line-strong bg-surface-base text-brand-500 focus:ring-2 focus:ring-brand-300 focus:ring-offset-0"
                            />
                            <label
                                for="is_active"
                                class="cursor-pointer select-none text-xs text-ink-secondary"
                            >
                                إتاحة هذه الباقة للتسجيل والاشتراك
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-end gap-2 border-t border-line-soft pt-4"
                        >
                            <button
                                type="button"
                                @click="showModal = false"
                                class="cursor-pointer px-4 py-2 text-xs font-semibold text-ink-muted transition hover:text-ink-primary"
                            >
                                إلغاء
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-brand-400 disabled:opacity-50"
                            >
                                <span v-if="form.processing"
                                    >جاري الحفظ...</span
                                >
                                <span v-else>حفظ</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
