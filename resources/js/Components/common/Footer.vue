<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer id="contact" class="border-t border-slate-900 bg-slate-950 text-slate-400 text-xs py-14 px-6 sm:px-12">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
            <!-- عمود النادي -->
            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-base">
                        🏋️
                    </span>
                    <span class="text-base font-bold text-white">نادي الأبطال الرياضي</span>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
                    صرح رياضي متكامل مخصص لبناء الأجسام واللياقة البدنية وقوة التحمل. نقدم بيئة تدريبية احترافية مدعومة بأنظمة رقمية متطورة لتنظيم الحضور والدفع الإلكتروني.
                </p>
                <div class="flex items-center gap-3 text-amber-500 font-mono text-xs">
                    <span>⚡ الدفع متاح عبر شام كاش أو نقداً في الصالة</span>
                </div>
            </div>

            <!-- ساعات العمل -->
            <div class="space-y-3">
                <h4 class="text-sm font-bold text-white">ساعات العمل الرسمية</h4>
                <ul class="space-y-2 text-slate-400">
                    <li class="flex justify-between">
                        <span>فترة الرجال:</span>
                        <strong class="text-slate-200">02:00 م - 11:30 م</strong>
                    </li>
                    <li class="flex justify-between">
                        <span>فترة السيدات:</span>
                        <strong class="text-slate-200">09:00 ص - 01:30 م</strong>
                    </li>
                    <li class="flex justify-between">
                        <span>يوم الجمعة:</span>
                        <strong class="text-amber-400">عطلة أسبوعية</strong>
                    </li>
                </ul>
            </div>

            <!-- معلومات التواصل -->
            <div class="space-y-3">
                <h4 class="text-sm font-bold text-white">العنوان والاستفسار</h4>
                <ul class="space-y-2 text-slate-400">
                    <li>📍 شارع الصالات الرياضية الرئيسي</li>
                    <li>📞 هاتف الاستقبال: 0987654321</li>
                    <li>💬 الدعم عبر واتساب متاح طيلة الدوام</li>
                </ul>
            </div>
        </div>

        <div class="max-w-6xl mx-auto pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-500">
            <p>© {{ currentYear }} نادي الأبطال - جميع الحقوق محفوظة.</p>
            <p class="font-mono text-[11px] text-slate-600">Built with Laravel 13, Vue 3 & Neon PostgreSQL</p>
        </div>
    </footer>
</template>
