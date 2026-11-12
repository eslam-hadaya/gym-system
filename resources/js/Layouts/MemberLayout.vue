<script setup>
import { ref, computed } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import MemberSidebar from "@/Components/Member/MemberSidebar.vue";
import MemberNavbar from "@/Components/Member/MemberTopNavbar.vue";

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
        }
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
        }
    );
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex" dir="rtl">
        <MemberSidebar />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <MemberNavbar
                @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
            />

            <main
                class="flex-1 p-6 sm:p-8 overflow-y-auto max-w-6xl mx-auto w-full"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
