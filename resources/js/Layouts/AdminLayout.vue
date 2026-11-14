<script setup>
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminSidebar from "@/Components/Admin/AdminSidebar.vue";
import AdminNavbar from "@/Components/Admin/AdminTopNavbar.vue";

const page = usePage();
const isSidebarOpen = ref(false);

// إغلاق السايدبار تلقائياً عند تغيير الصفحة
watch(
    () => page.url,
    () => {
        isSidebarOpen.value = false;
    },
);
</script>

<template>
    <div
        class="flex min-h-screen bg-surface-base text-ink-primary transition-colors duration-300"
        dir="rtl"
    >
        <!-- ============ السايدبار (ديسكتوب) ============ -->
        <div class="sticky top-0 hidden h-screen md:block">
            <AdminSidebar />
        </div>

        <!-- ============ Drawer السايدبار (موبايل) ============ -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isSidebarOpen" class="fixed inset-0 z-50 md:hidden">
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="isSidebarOpen = false"
                ></div>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-x-0"
                    leave-to-class="translate-x-full"
                >
                    <div
                        class="absolute inset-y-0 right-0 w-64 max-w-[80vw] shadow-2xl"
                    >
                        <AdminSidebar />
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ============ المحتوى الرئيسي ============ -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <AdminNavbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />

            <main
                class="flex-1 overflow-y-auto bg-surface-base p-4 sm:p-6 lg:p-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
