import { ref, computed, onMounted, onUnmounted } from "vue";

export function useRemainingDays(subscription) {
    const remainingDays = computed(() => {
        if (!subscription.value || !subscription.value.end_date) return 0;

        const end = new Date(subscription.value.end_date).setHours(
            23,
            59,
            59,
            999,
        );
        const now = new Date().getTime();
        const diff = end - now;

        if (diff <= 0) return 0;
        return Math.ceil(diff / (1000 * 60 * 60 * 24));
    });

    const isExpiringSoon = computed(() => {
        return remainingDays.value > 0 && remainingDays.value <= 5;
    });

    const isExpired = computed(() => {
        return remainingDays.value === 0;
    });

    return {
        remainingDays,
        isExpiringSoon,
        isExpired,
    };
}

// الدالة المطلوبة من Index.vue
export function useCountdown(targetDate) {
    const days = ref(0);
    const hours = ref(0);
    const minutes = ref(0);
    const seconds = ref(0);
    let timer = null;

    const calculate = () => {
        // في حال كانت القيمة المرسلة هي مرجع (ref) أو تاريخ مباشر
        const dateVal =
            targetDate.value !== undefined ? targetDate.value : targetDate;
        if (!dateVal) return;

        const end = new Date(dateVal).getTime();
        const now = new Date().getTime();
        const diff = end - now;

        if (diff <= 0) {
            days.value = 0;
            hours.value = 0;
            minutes.value = 0;
            seconds.value = 0;
            if (timer) clearInterval(timer);
            return;
        }

        days.value = Math.floor(diff / (1000 * 60 * 60 * 24));
        hours.value = Math.floor((diff / (1000 * 60 * 60)) % 24);
        minutes.value = Math.floor((diff / 1000 / 60) % 60);
        seconds.value = Math.floor((diff / 1000) % 60);
    };

    onMounted(() => {
        calculate();
        timer = setInterval(calculate, 1000);
    });

    onUnmounted(() => {
        if (timer) clearInterval(timer);
    });

    return { days, hours, minutes, seconds };
}
