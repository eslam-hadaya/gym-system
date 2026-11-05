<script setup>
const features = [
    {
        title: 'دخول سريع بنظام QR الذكي',
        description: 'بطاقة رقمية على هاتفك تُمسح في ثانية واحدة على الباب دون الحاجة لبطاقات ورقية أو انتظار الموظف.',
        icon: '📷'
    },
    {
        title: 'تجهيزات ومعدات دولية',
        description: 'أحدث آلات الوزن الحر والأجهزة الهيدروليكية المعتمدة لعزل العضلات بأمان وكفاءة تامة.',
        icon: '💪'
    },
    {
        title: 'طرق دفع محلية مرنة',
        description: 'إمكانية تجديد اشتراكك مباشرة من حسابك عبر شام كاش (Sham Cash) برقم المعاملة أو كاش في الصندوق.',
        icon: '💳'
    },
    {
        title: 'إشراف كباتن متخصصين',
        description: 'كادر تدريبي متفرغ لمتابعة خططك التدريبية، تصحيح الأداء الحركي، وتقديم التوجيه الغذائي السليم.',
        icon: '🎯'
    }
];
</script>

<template>
    <section id="features" class="py-24 px-6 sm:px-12 max-w-6xl mx-auto border-b border-slate-900">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-amber-400 tracking-wider uppercase">// لماذا نادي الأبطال؟</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white">كل ما يحتاجه الرياضي الحقيقي</h2>
            <p class="text-sm text-slate-400">صممنا تفاصيل الصالة والنظام الرقمي لتجعل تجربتك خالية من أي تعطيل أو هدر للوقت.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                v-for="(feature, idx) in features"
                :key="idx"
                class="bg-slate-900/60 border border-slate-800/80 hover:border-amber-500/40 rounded-2xl p-6 transition duration-300 hover:-translate-y-1 group"
            >
                <span class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-2xl mb-4 group-hover:bg-amber-500/10 transition">
                    {{ feature.icon }}
                </span>
                <h3 class="text-base font-bold text-white mb-2">{{ feature.title }}</h3>
                <p class="text-xs text-slate-400 leading-relaxed">{{ feature.description }}</p>
            </div>
        </div>
    </section>
</template>
