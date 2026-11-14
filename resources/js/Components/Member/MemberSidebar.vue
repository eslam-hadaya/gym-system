<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    QrCode,
    CreditCard,
    History,
    LogOut,
    Dumbbell,
    User,
} from "@lucide/vue";

const page = usePage();

const user = computed(() => page.props.auth?.user || { name: "اللاعب" });
const member = computed(() => page.props.member || null);

const currentUrl = computed(() => page.url);

const isActive = (url) => {
    if (url === "/member/dashboard") {
        return currentUrl.value === "/member/dashboard";
    }
    return currentUrl.value.startsWith(url);
};

const navItems = [
    { name: "بطاقتي الرقمية (QR)", url: "/member/dashboard", icon: QrCode },
    {
        name: "اشتراكي والتجديد",
        url: "/member/dashboard/subscriptions",
        icon: CreditCard,
    },
    {
        name: "سجل حركات الحضور",
        url: "/member/dashboard/history",
        icon: History,
    },
];

const userInitial = computed(() => {
    const name = user.value?.name?.trim() || "لاعب";
    return name.charAt(0).toUpperCase();
});
</script>

<template>
    <aside
        dir="rtl"
        class="sticky top-0 flex h-screen w-64 shrink-0 flex-col justify-between border-l border-line-soft bg-surface-elevated transition-colors duration-300"
    >
        <!-- ============ العلوي: الشعار والروابط ============ -->
        <div>
            <!-- الشعار -->
            <Link
                href="/"
                class="flex h-16 items-center gap-3 border-b border-line-soft px-4 sm:h-20 sm:px-5"
                aria-label="نادي الأبطال — الرئيسية"
            >
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-glow"
                >
                    <Dumbbell class="h-5 w-5" :stroke-width="2.2" />
                </span>
                <div class="min-w-0">
                    <h2
                        class="truncate text-sm font-black text-ink-primary sm:text-base"
                    >
                        نادي الأبطال
                    </h2>
                    <span
                        class="block truncate text-[10px] font-medium text-brand-600 dark:text-brand-400"
                    >
                        بوابة اللاعب الرياضي
                    </span>
                </div>
            </Link>

            <!-- روابط التنقل -->
            <nav class="p-3 sm:p-4">
                <p
                    class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-ink-muted"
                >
                    حسابي والخدمات
                </p>

                <div class="space-y-1.5 mt-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.url"
                        :href="item.url"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                        :class="
                            isActive(item.url)
                                ? 'bg-brand-500 text-white shadow-glow'
                                : 'text-ink-secondary hover:bg-glass-bg-hover hover:text-ink-primary'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-4 w-4 shrink-0"
                            :stroke-width="2"
                        />
                        <span class="truncate">{{ item.name }}</span>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- ============ السفلي: معلومات المستخدم ============ -->
        <div class="border-t border-line-soft p-3 sm:p-4 mt-auto">
            <div class="flex items-center gap-3">
                <!-- أفاتار -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line-strong bg-glass-bg text-sm font-bold text-brand-600 dark:text-brand-400"
                >
                    <User
                        v-if="!user?.name"
                        class="h-5 w-5"
                        :stroke-width="2"
                    />
                    <span v-else>{{ userInitial }}</span>
                </div>

                <!-- الاسم والكود -->
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-ink-primary">
                        {{ user.name }}
                    </p>
                    <p
                        v-if="member?.member_code"
                        class="truncate font-mono text-[10px] font-medium text-brand-600 dark:text-brand-400"
                        dir="ltr"
                    >
                        {{ member.member_code }}
                    </p>
                    <p v-else class="truncate text-[10px] text-ink-muted">
                        مشترك رياضي
                    </p>
                </div>

                <!-- تسجيل الخروج -->
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    aria-label="تسجيل الخروج"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-ink-muted transition-colors duration-200 hover:bg-danger-500/10 hover:text-danger-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400"
                >
                    <LogOut class="h-4 w-4" :stroke-width="2" />
                </Link>
            </div>
        </div>
    </aside>
</template>
