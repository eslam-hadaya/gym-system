import { ref, watch } from "vue";

// متغير مشترك بين كل المكونات (Singleton)
const theme = ref("light");
let initialized = false;

export function useTheme() {
    if (!initialized && typeof window !== "undefined") {
        initialized = true;

        // 1. قراءة الوضع من الـ localStorage أو من إعدادات النظام
        const stored = localStorage.getItem("theme");
        const prefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)",
        ).matches;

        theme.value = stored || (prefersDark ? "dark" : "light");

        // 2. تطبيق الوضع على الـ DOM
        const applyTheme = (val) => {
            const root = document.documentElement;
            if (val === "dark") {
                root.classList.add("dark");
            } else {
                root.classList.remove("dark");
            }
            localStorage.setItem("theme", val);
        };

        // 3. مراقبة التغييرات وتطبيقها فوراً
        watch(theme, (val) => applyTheme(val), { immediate: true });

        // 4. مراقبة تغييرات نظام التشغيل (إذا لم يقم المستخدم باختيار يدوي)
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (!localStorage.getItem("theme")) {
                    theme.value = e.matches ? "dark" : "light";
                }
            });
    }

    const toggle = () => {
        theme.value = theme.value === "dark" ? "light" : "dark";
    };

    const setTheme = (value) => {
        if (value === "dark" || value === "light") {
            theme.value = value;
        }
    };

    return { theme, toggle, setTheme };
}
