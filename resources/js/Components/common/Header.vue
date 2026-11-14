<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import {
    Dumbbell,
    LayoutDashboard,
    QrCode,
    LogIn,
    UserPlus,
    Menu,
    X,
} from "@lucide/vue";
import ThemeToggle from "@/components/ThemeToggle.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const dashboardUrl = computed(() => {
    if (!user.value) return "/login";
    return user.value.role === "admin"
        ? "/admin/dashboard"
        : "/member/dashboard";
});

const navLinks = [
    { href: "#about", label: "من نحن" },
    { href: "#features", label: "المميزات" },
    { href: "#plans", label: "الباقات" },
    { href: "#contact", label: "تواصل" },
];

const mobileOpen = ref(false);
watch(
    () => page.url,
    () => {
        mobileOpen.value = false;
    },
);
</script>

<template>
    <header
        dir="rtl"
        class="sticky top-0 z-50 border-b border-line-soft bg-surface-base/80 backdrop-blur-glass transition-colors duration-300"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-3 sm:h-20 sm:px-6 lg:px-8"
        >
            <!-- الشعار -->
            <Link
                href="/"
                class="flex shrink-0 items-center gap-2.5 sm:gap-3"
                aria-label="نادي الأبطال — الصفحة الرئيسية"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl sm:h-11 sm:w-11 sm:rounded-2xl bg-brand-500 text-white shadow-glow"
                >
                    <Dumbbell
                        class="h-5 w-5 sm:h-6 sm:w-6"
                        :stroke-width="2.2"
                    />
                </span>
                <div class="leading-tight">
                    <span
                        class="block text-sm font-black tracking-tight text-ink-primary sm:text-lg"
                    >
                        نادي الأبطال
                    </span>
                    <span
                        class="block text-[10px] font-semibold tracking-wide text-brand-600 dark:text-brand-400"
                    >
                        Champions Gym
                    </span>
                </div>
            </Link>

            <!-- روابط الأقسام (ديسكتوب) -->
            <nav
                class="hidden items-center gap-6 text-sm font-medium text-ink-secondary md:flex lg:gap-8"
                aria-label="أقسام الموقع"
            >
                <a
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="transition-colors duration-200 hover:text-brand-600 dark:hover:text-brand-400"
                >
                    {{ link.label }}
                </a>
            </nav>

            <!-- الأزرار -->
            <div class="flex items-center gap-2 sm:gap-3">
                <ThemeToggle />

                <!-- مسجل دخول -->
                <template v-if="user">
                    <Link
                        :href="dashboardUrl"
                        class="hidden items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base sm:inline-flex sm:text-sm"
                    >
                        <component
                            :is="
                                user.role === 'admin' ? LayoutDashboard : QrCode
                            "
                            class="h-4 w-4"
                            :stroke-width="2"
                        />
                        {{
                            user.role === "admin" ? "لوحة الإدارة" : "داشبوردي"
                        }}
                    </Link>
                </template>

                <!-- زائر -->
                <template v-else>
                    <Link
                        href="/login"
                        class="hidden items-center gap-1.5 rounded-xl border border-line-strong px-4 py-2.5 text-xs font-semibold text-ink-secondary transition-colors duration-200 hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 sm:inline-flex sm:text-sm"
                    >
                        <LogIn class="h-4 w-4" :stroke-width="2" />
                        تسجيل الدخول
                    </Link>

                    <Link
                        href="/register"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3 py-2 text-xs font-bold text-white shadow-glow transition-colors duration-200 hover:bg-brand-400 active:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base sm:px-5 sm:py-2.5 sm:text-sm"
                    >
                        <UserPlus class="h-4 w-4" :stroke-width="2" />
                        انضم إلينا
                    </Link>
                </template>

                <!-- زر قائمة الموبايل -->
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-line-strong text-ink-secondary transition-colors duration-200 hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 md:hidden"
                    :aria-expanded="mobileOpen"
                    aria-controls="mobile-menu"
                    aria-label="فتح القائمة"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Menu
                        v-if="!mobileOpen"
                        class="h-5 w-5"
                        :stroke-width="2"
                    />
                    <X v-else class="h-5 w-5" :stroke-width="2" />
                </button>
            </div>
        </div>

        <!-- قائمة الموبايل -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="mobileOpen"
                id="mobile-menu"
                class="border-t border-line-soft bg-surface-base px-4 pb-5 pt-3 md:hidden"
            >
                <nav class="flex flex-col gap-1" aria-label="أقسام الموقع">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="rounded-lg px-3 py-2.5 text-sm font-medium text-ink-secondary transition-colors hover:bg-glass-bg-hover hover:text-ink-primary"
                        @click="mobileOpen = false"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <div class="mt-4 flex flex-col gap-2">
                    <template v-if="user">
                        <Link
                            :href="dashboardUrl"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white shadow-glow"
                        >
                            <component
                                :is="
                                    user.role === 'admin'
                                        ? LayoutDashboard
                                        : QrCode
                                "
                                class="h-4 w-4"
                                :stroke-width="2"
                            />
                            {{
                                user.role === "admin"
                                    ? "لوحة الإدارة"
                                    : "داشبوردي"
                            }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-line-strong px-4 py-3 text-sm font-semibold text-ink-secondary"
                        >
                            <LogIn class="h-4 w-4" :stroke-width="2" />
                            تسجيل الدخول
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white shadow-glow"
                        >
                            <UserPlus class="h-4 w-4" :stroke-width="2" />
                            انضم إلينا
                        </Link>
                    </template>
                </div>
            </div>
        </Transition>
    </header>
</template>
