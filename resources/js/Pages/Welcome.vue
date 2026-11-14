<script setup>
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/common/Header.vue";
import Footer from "@/Components/common/Footer.vue";
import HeroSection from "@/Components/welcome/HeroSection.vue";
import AboutSection from "@/Components/welcome/AboutSection.vue";
import FeaturesSection from "@/Components/welcome/FeaturesSection.vue";
import PlansSection from "@/Components/welcome/PlansSection.vue";

defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="نادي الأبطال الرياضي - وجهتك للقمة" />

    <div
        dir="rtl"
        class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-black scroll-smooth"
    >
        <Header />

        <main class="flex-1">
            <HeroSection />
            <AboutSection />
            <FeaturesSection />
            <PlansSection :plans="plans" />
        </main>

        <Footer />
    </div>
</template>
