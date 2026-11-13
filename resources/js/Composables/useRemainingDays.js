import { computed, onMounted, onUnmounted, ref } from "vue";

const getDamascusDate = () => {
    const parts = new Intl.DateTimeFormat("en-US", {
        timeZone: "Asia/Damascus",
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).formatToParts(new Date());
    const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));

    return `${values.year}-${values.month}-${values.day}`;
};

export function useRemainingDays(getEndDate) {
    const today = ref(getDamascusDate());
    let intervalId;

    onMounted(() => {
        intervalId = window.setInterval(() => {
            today.value = getDamascusDate();
        }, 60_000);
    });

    onUnmounted(() => {
        window.clearInterval(intervalId);
    });

    return computed(() => {
        const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(getEndDate() || "");

        if (!match) {
            return 0;
        }

        const [, year, month, day] = match.map(Number);
        const endDate = Date.UTC(year, month - 1, day);
        const currentDate = Date.UTC(
            ...today.value.split("-").map(Number),
        );

        return Math.max(0, (endDate - currentDate) / 86_400_000);
    });
}

export function useCountdown(getDeadline) {
    const currentTime = ref(Date.now());
    let intervalId;

    onMounted(() => {
        intervalId = window.setInterval(() => {
            currentTime.value = Date.now();
        }, 1000);
    });

    onUnmounted(() => {
        window.clearInterval(intervalId);
    });

    return computed(() => {
        const deadline = getDeadline();
        const deadlineTime = deadline ? Date.parse(deadline) : Number.NaN;
        const totalSeconds = Number.isFinite(deadlineTime)
            ? Math.max(0, Math.ceil((deadlineTime - currentTime.value) / 1000))
            : 0;

        return {
            isActive: Number.isFinite(deadlineTime),
            isComplete: Number.isFinite(deadlineTime) && totalSeconds === 0,
            days: Math.floor(totalSeconds / 86_400),
            hours: Math.floor((totalSeconds % 86_400) / 3_600),
            minutes: Math.floor((totalSeconds % 3_600) / 60),
            seconds: totalSeconds % 60,
        };
    });
}
