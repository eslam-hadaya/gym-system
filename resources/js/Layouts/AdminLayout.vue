<script setup>
import AdminSidebar from "@/Components/Admin/AdminSidebar.vue";
import AdminNavbar from "@/Components/Admin/AdminTopNavbar.vue";
</script>

<template>
    <div
        class="flex min-h-screen bg-surface-base text-ink-primary transition-colors duration-300"
        dir="rtl"
    >
        <!-- السايدبار على اليمين -->
        <AdminSidebar />

        <!-- المساحة الرئيسية مع الهيدر والمحتوى -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <AdminNavbar />

            <main
                class="flex-1 overflow-y-auto bg-surface-base p-4 sm:p-6 lg:p-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
