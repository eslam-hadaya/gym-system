<script setup>
import { computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { Menu, ShieldCheck, LogOut } from "@lucide/vue";

defineEmits(["toggleSidebar"]);

const page = usePage();

const user = computed(() => {
    return (
        page.props.auth?.user || {
            name: "المسؤول",
            email: "admin@gym.sy",
        }
    );
});

const todayDate = new Intl.DateTimeFormat("ar-SY", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
}).format(new Date());

const handleLogout = () => {
    router.post("/logout");
};
</script>

<template>
    <header
        dir="rtl"
        class="sticky top-0 z-30 flex h-16 items-center justify-between gap-2 border-b border-line-soft bg-surface-base/80 px-3 backdrop-blur-glass transition-colors duration-300 sm:h-20 sm:px-6"
    >
        <!-- ============ الجهة اليمنى: زر السايدبار وتاريخ الوردية ============ -->
        <div class="flex items-center gap-3 sm:gap-4">
            <button
                type="button"
                @click="$emit('toggleSidebar')"
                aria-label="فتح القائمة"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line-strong text-ink-secondary transition-colors hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 md:hidden"
            >
                <Menu class="h-5 w-5" :stroke-width="2" />
            </button>

            <div class="hidden flex-col sm:flex">
                <span class="text-xs font-bold text-ink-primary">{{
                    todayDate
                }}</span>
                <span class="text-[10px] font-medium text-ink-muted">
                    الوردية الحالية: استقبال وتسجيل
                </span>
            </div>
        </div>

        <!-- ============ الجهة اليسرى: المؤشرات التشغيلية وهوية المدير ============ -->
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- اسم المدير -->
            <div
                class="flex items-center gap-2.5 border-r border-line-soft pr-2.5 sm:pr-3.5"
            >
               
                <div class="hidden text-right md:block">
                    <p
                        class="max-w-[120px] truncate text-xs font-bold leading-tight text-ink-primary"
                    >
                        {{ user.name }}
                    </p>
                    <p class="text-[10px] leading-tight text-ink-muted">
                        صلاحية إدارية كاملة
                    </p>
                </div>
            </div>

            <!-- زر تسجيل الخروج -->
            <button
                type="button"
                @click="handleLogout"
                aria-label="تسجيل الخروج"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2 text-ink-muted transition-colors hover:bg-danger-500/10 hover:text-danger-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400 sm:px-3"
            >
                <LogOut class="h-4 w-4 shrink-0" :stroke-width="2" />
                <span class="hidden text-xs font-bold sm:inline">خروج</span>
            </button>
        </div>
    </header>
</template>
