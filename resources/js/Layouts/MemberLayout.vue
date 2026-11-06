<script setup>
import { ref } from 'vue';
import MemberSidebar from '@/Components/Member/MemberSidebar.vue';
import MemberNavbar from '@/Components/Member/MemberTopNavbar.vue';

const isSidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex" dir="rtl">
        <MemberSidebar />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <MemberNavbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />

            <main class="flex-1 p-6 sm:p-8 overflow-y-auto max-w-6xl mx-auto w-full">
                <slot />
            </main>
        </div>
    </div>
</template>
