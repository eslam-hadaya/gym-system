<script setup>
import { ref } from 'vue';
import MemberSidebar from '@/Components/Member/MemberSidebar.vue';
import MemberTopNavbar from '@/Components/Member/MemberTopNavbar.vue';

// حالة فتح/إغلاق القائمة الجانبية للشاشات الصغيرة
const mobileSidebarOpen = ref(false);
</script>

<template>
    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex font-sans antialiased selection:bg-amber-500 selection:text-black">
        <!-- 1. السايدبار الجانبي للاعب على جهة اليسار -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 transition-transform duration-300 md:static md:translate-x-0',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <MemberSidebar />
        </div>

        <!-- خلفية معتمة للهواتف عند فتح السايدبار -->
        <div
            v-if="mobileSidebarOpen"
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 md:hidden"
        ></div>

        <!-- 2. منطقة العمل الرئيسية لبوابة اللاعب -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- الشريط العلوي للاعب -->
            <MemberTopNavbar @toggleSidebar="mobileSidebarOpen = !mobileSidebarOpen" />

            <!-- مساحة حقن بطاقة اللاعب وسجل اشتراكاته -->
            <main class="flex-1 p-6 sm:p-8 overflow-y-auto">
                <div class="max-w-5xl mx-auto space-y-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
