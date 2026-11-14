<script setup>
import { computed, ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { Menu, LogOut, RefreshCw, Loader2, QrCode } from "@lucide/vue";
// استدعاء مسار ThemeToggle الصحيح بناءً على صورتك:
import ThemeToggle from "@/Components/ThemeToggle.vue";

defineEmits(["toggleSidebar"]);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: "اللاعب" });
const member = computed(() => page.props.member || null);
const subscription = computed(
    () => page.props.subscription || page.props.currentSubscription || null,
);

const status = computed(() => {
    if (subscription.value?.status) return subscription.value.status;
    return member.value?.status || "pending";
});

const memberCode = computed(() => member.value?.member_code || "—");

const todayDate = new Intl.DateTimeFormat("ar-SY", {
    weekday: "long",
    day: "numeric",
    month: "long",
}).format(new Date());

const statusConfig = computed(() => {
    const s = status.value;
    if (s === "active" || s === "verified") {
        return {
            label: "اشتراكك سارٍ",
            short: "سارٍ",
            classes:
                "bg-success-500/10 border-success-500/30 text-success-600 dark:text-success-400",
        };
    }
    if (s === "rejected" || s === "cancelled") {
        return {
            label: "تم رفض الطلب",
            short: "مرفوض",
            classes: "bg-danger-500/10 border-danger-500/30 text-danger-500",
        };
    }
    if (s === "pending") {
        return {
            label: "بانتظار الاعتماد",
            short: "معلّق",
            classes:
                "bg-warning-500/10 border-warning-500/30 text-warning-600 dark:text-warning-400",
        };
    }
    return {
        label: "غير مشترك",
        short: "منتهٍ",
        classes: "bg-glass-bg border-line-strong text-ink-muted",
    };
});

const isLoggingOut = ref(false);
const isRefreshing = ref(false);

const handleLogout = () => {
    if (!confirm("هل تريد تسجيل الخروج من حسابك؟")) return;
    isLoggingOut.value = true;
    router.post(
        "/logout",
        {},
        {
            onFinish: () => {
                isLoggingOut.value = false;
            },
        },
    );
};

const refreshStatus = () => {
    isRefreshing.value = true;
    router.reload({
        only: ["member", "subscription", "currentSubscription"],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};
</script>

<template>
    <header
        dir="rtl"
        class="sticky top-0 z-30 flex h-16 items-center justify-between gap-2 border-b border-line-soft bg-surface-base/80 px-3 backdrop-blur-glass transition-colors duration-300 sm:h-20 sm:px-6"
    >
        <!-- اليمين: زر القائمة + الترحيب -->
        <div class="flex min-w-0 items-center gap-2 sm:gap-4">
            <button
                @click="$emit('toggleSidebar')"
                aria-label="فتح القائمة"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line-strong text-ink-secondary transition-colors hover:bg-glass-bg-hover hover:text-ink-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 md:hidden"
            >
                <Menu class="h-5 w-5" :stroke-width="2" />
            </button>

            <div class="min-w-0">
                <p
                    class="truncate text-xs font-bold text-ink-primary sm:text-sm"
                >
                    أهلاً، {{ user.name }}
                </p>
                <p class="truncate text-[10px] text-ink-muted sm:text-[11px]">
                    {{ todayDate }}
                </p>
            </div>
        </div>

        <!-- اليسار: الحالة + الكود + الإجراءات -->
        <div class="flex items-center gap-1.5 sm:gap-3">
            <!-- كود العضوية -->
            <div
                class="hidden items-center gap-1.5 rounded-xl border border-line-strong bg-glass-bg px-2.5 py-1.5 sm:flex"
            >
                <QrCode class="h-3.5 w-3.5 text-ink-muted" :stroke-width="2" />
                <span
                    class="font-mono text-[11px] font-bold text-brand-600 dark:text-brand-400"
                    dir="ltr"
                >
                    {{ memberCode }}
                </span>
            </div>

            <!-- حالة الاشتراك -->
            <div
                class="inline-flex items-center gap-2 rounded-xl border px-2.5 py-1.5 text-xs font-bold sm:px-3"
                :class="statusConfig.classes"
            >
                <span class="hidden sm:inline">{{ statusConfig.label }}</span>
                <span class="sm:hidden">{{ statusConfig.short }}</span>
            </div>

            <!-- إعادة التحديث -->
            <button
                @click="refreshStatus"
                :disabled="isRefreshing"
                aria-label="تحديث الحالة"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-glass-bg-hover hover:text-ink-primary disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
            >
                <RefreshCw
                    class="h-4 w-4"
                    :class="{ 'animate-spin': isRefreshing }"
                    :stroke-width="2"
                />
            </button>

            <!-- تبديل الوضع -->
            <ThemeToggle />

            <!-- تسجيل الخروج -->
            <button
                @click="handleLogout"
                :disabled="isLoggingOut"
                aria-label="تسجيل الخروج"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2 text-ink-muted transition-colors hover:bg-danger-500/10 hover:text-danger-500 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400"
            >
                <Loader2
                    v-if="isLoggingOut"
                    class="h-4 w-4 animate-spin"
                    :stroke-width="2.5"
                />
                <LogOut v-else class="h-4 w-4" :stroke-width="2" />
                <span class="hidden text-xs font-bold sm:inline">خروج</span>
            </button>
        </div>
    </header>
</template>
