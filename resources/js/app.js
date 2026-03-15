// ── Axios must be configured BEFORE the Vue app boots ──────────────────────
import "@/plugins/axiosSetup";
// ────────────────────────────────────────────────────────────────────────────

import App from "@/app.vue";
import ThemeSwitcher from "@/components/ThemeSwitcher.vue";
import appRouter from "@/routes/AppRouter.js";
import { useThemeStore } from "@/store/theme";
import { createPinia } from "pinia";
import "primeicons/primeicons.css";
import Editor from "primevue/editor";
import "primevue/resources/primevue.min.css";
import "primevue/resources/themes/saga-blue/theme.css";
import "quill/dist/quill.snow.css";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const originalAddEventListener = EventTarget.prototype.addEventListener;
EventTarget.prototype.addEventListener = function (type, listener, options) {
    if (type === "touchstart") {
        if (options === undefined) {
            options = { passive: true };
        } else if (typeof options === "object" && options !== null) {
            options.passive = true;
        }
    }
    originalAddEventListener.call(this, type, listener, options);
};

const app = createApp(App);

app.use(createPinia());
app.use(appRouter);

app.use(Toast, {
    timeout: 3000,
    position: "top-right",
});

app.component("theme-switcher", ThemeSwitcher);
app.component("Editor", Editor);

// Global directive: v-click-outside
// Usage: v-click-outside="handler" — calls handler when a click occurs outside the element.
app.directive("click-outside", {
    mounted(el, binding) {
        el._clickOutsideHandler = (event) => {
            if (!el.contains(event.target)) {
                binding.value(event);
            }
        };
        document.addEventListener("mousedown", el._clickOutsideHandler);
    },
    unmounted(el) {
        document.removeEventListener("mousedown", el._clickOutsideHandler);
        delete el._clickOutsideHandler;
    },
});

app.mount("#app");

const themeStore = useThemeStore();
themeStore.applyTheme();
