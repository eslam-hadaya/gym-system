<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Components/Common/Header.vue';
import Footer from '@/Components/Common/Footer.vue';
import HeroSection from '@/Components/Welcome/HeroSection.vue';
import AboutSection from '@/Components/Welcome/AboutSection.vue';
import FeaturesSection from '@/Components/Welcome/FeaturesSection.vue';
import PlansSection from '@/Components/Welcome/PlansSection.vue';
defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="نادي الأبطال الرياضي - وجهتك للقمة" />

    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-black scroll-smooth">
        <!-- 1. الهيدر -->
        <Header />

        <!-- 2. الأقسام المجمعة -->
        <main class="flex-1">
            <HeroSection />
            <AboutSection />
            <FeaturesSection />
            <PlansSection :plans="plans" />
        </main>

        <!-- 3. الفوتر -->
        <Footer />
    </div>
</template>
