<script setup>
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminNavbar from '@/Components/Admin/AdminTopNavbar.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex" dir="rtl">
        <!-- السايدبار على اليمين -->
        <AdminSidebar />

        <!-- المساحة الرئيسية مع الهيدر والمحتوى -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <AdminNavbar />

            <main class="flex-1 p-6 sm:p-8 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
