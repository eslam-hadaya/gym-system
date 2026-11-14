<script setup>
import { ref, computed, watch } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import MemberSidebar from "@/Components/Member/MemberSidebar.vue";
import MemberNavbar from "@/Components/Member/MemberTopNavbar.vue";
import { Menu, X } from "@lucide/vue";

defineEmits(["toggle-sidebar"]);

const page = usePage();
const isOpen = ref(false);
const isSidebarOpen = ref(false);

// قراءة آمنة حتى لو تأخرت بيانات auth
const notifications = computed(() => {
    return page.props.auth?.notifications || [];
});

const unreadCount = computed(() => {
    return Number(page.props.auth?.unread_notifications_count) || 0;
});

const markAsRead = (id, url) => {
    if (!id) return;
    router.post(
        `/member/dashboard/notifications/${id}/read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isOpen.value = false;
                if (url && url !== "#") router.visit(url);
            },
        },
    );
};

const markAllAsRead = () => {
    router.post(
        "/member/dashboard/notifications/read-all",
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isOpen.value = false;
            },
        },
    );
};

// إغلاق القائمة تلقائياً عند تغيير الصفحة
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
        <!-- ============ السايدبار (ثابت على الشاشات الكبيرة) ============ -->
        <div class="sticky top-0 hidden h-screen md:block">
            <MemberSidebar />
        </div>

        <!-- ============ Drawer السايدبار (للموبايل) ============ -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isSidebarOpen" class="fixed inset-0 z-50 md:hidden">
                <!-- Overlay -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="isSidebarOpen = false"
                ></div>

                <!-- Panel -->
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
                        <MemberSidebar />
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ============ المحتوى الرئيسي ============ -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <MemberNavbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />

            <main
                class="mx-auto w-full max-w-6xl flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
