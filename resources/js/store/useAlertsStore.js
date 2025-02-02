import { defineStore } from "pinia";
import { toast } from "vue3-toastify";

export const useAlertsStore = defineStore("alerts", {
    state: () => ({}),
    actions: {
        showSuccessToast(message) {
            toast.info(message, {
                autoClose: 2000,
                position: "bottom-right",
            });
        },
        showErrortost(message) {
            toast.error(message, {
                autoClose: 2000,
                position: "bottom-right",
            });
        },
    },
});

