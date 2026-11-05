<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const count = ref(0);
const status = ref('نظام الجيم جاهز للانطلاق 🚀');
</script>

<template>
    <Head title="اختبار إعدادات النظام" />

    <div dir="rtl" class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6 selection:bg-amber-500 selection:text-black">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <!-- خلفية جمالية خفيفة للتأكد من قراءة كلاسات التدرج والـ Blur -->
            <div class="absolute -top-12 -left-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 font-bold text-lg border border-amber-500/20">
                    🏋️
                </span>
                <div>
                    <h1 class="text-lg font-bold text-white">إعداد واجهة نظام الجيم</h1>
                    <p class="text-xs text-slate-400">Laravel 13 + Vue 3 + Tailwind CSS</p>
                </div>
            </div>

            <!-- بطاقة فحص الحالة -->
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 mb-6">
                <span class="text-xs text-slate-400 block mb-1">حالة البيئة:</span>
                <p class="text-sm font-semibold text-emerald-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ status }}
                </p>
            </div>

            <!-- اختبار تفاعلية Vue عبر زر عداد بسيط -->
            <div class="text-center">
                <button
                    @click="count++"
                    class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold py-3 px-6 rounded-xl transition duration-200 shadow-lg shadow-amber-500/10 active:scale-98 cursor-pointer text-sm"
                >
                    اختبار التفاعل (النقرات: {{ count }})
                </button>
            </div>
        </div>
    </div>
</template>
