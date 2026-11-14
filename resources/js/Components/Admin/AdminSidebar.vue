<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    LayoutDashboard,
    Users,
    ScanLine,
    CreditCard,
    Wallet,
    ShieldCheck,
    LogOut,
    Dumbbell,
} from "@lucide/vue";

const page = usePage();

const user = computed(
    () =>
        page.props.auth?.user || {
            name: "المدير العام",
            email: "admin@gym.sy",
        },
);

const currentUrl = computed(() => page.url);

const isActive = (url) => {
    if (url === "/admin/dashboard") {
        return currentUrl.value === "/admin/dashboard";
    }
    return currentUrl.value.startsWith(url);
};

const navItems = [
    {
        name: "نظرة عامة",
        url: "/admin/dashboard",
        icon: LayoutDashboard,
        badge: null,
    },
    {
        name: "إدارة اللاعبين",
        url: "/admin/dashboard/members",
        icon: Users,
        badge: null,
    },
    {
        name: "مكتب الاستقبال (QR)",
        url: "/admin/dashboard/checkin",
        icon: ScanLine,
        badge: "سريع",
    },
    {
        name: "الباقات والأسعار",
        url: "/admin/dashboard/plans",
        icon: CreditCard,
        badge: null,
    },
    {
        name: "الصندوق وشام كاش",
        url: "/admin/dashboard/finance",
        icon: Wallet,
        badge: null,
    },
];
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
                href="/admin/dashboard"
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
                        لوحة الإدارة
                    </span>
                </div>
            </Link>

            <!-- روابط التنقل -->
            <nav class="p-3 sm:p-4">
                <p
                    class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-ink-muted"
                >
                    إدارة المنظومة
                </p>

                <div class="mt-1 space-y-1.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.url"
                        :href="item.url"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                        :class="
                            isActive(item.url)
                                ? 'bg-brand-500 text-white shadow-glow'
                                : 'text-ink-secondary hover:bg-glass-bg-hover hover:text-ink-primary'
                        "
                    >
                        <div class="flex items-center gap-3">
                            <component
                                :is="item.icon"
                                class="h-4 w-4 shrink-0"
                                :stroke-width="2"
                            />
                            <span class="truncate">{{ item.name }}</span>
                        </div>

                        <span
                            v-if="item.badge"
                            class="rounded-md px-2 py-0.5 font-mono text-[10px] font-bold"
                            :class="
                                isActive(item.url)
                                    ? 'bg-black/20 text-white'
                                    : 'border border-brand-500/20 bg-brand-500/10 text-brand-600 dark:text-brand-400'
                            "
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- ============ السفلي: هوية المدير وزر الخروج ============ -->
        <div class="mt-auto border-t border-line-soft p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <!-- أفاتار المدير -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line-strong bg-glass-bg text-brand-600 dark:text-brand-400"
                >
                    <ShieldCheck class="h-5 w-5" :stroke-width="2" />
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-ink-primary">
                        {{ user.name }}
                    </p>
                    <p
                        class="truncate text-[10px] font-medium text-brand-600 dark:text-brand-400"
                    >
                        مسؤول النظام
                    </p>
                </div>

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
