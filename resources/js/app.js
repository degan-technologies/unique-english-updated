import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";
import Editor from "primevue/editor";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import "primeicons/primeicons.css";
import "primevue/resources/primevue.min.css"; 
import "primevue/resources/themes/saga-blue/theme.css";
import "quill/dist/quill.snow.css";
import ThemeSwitcher from "@/components/ThemeSwitcher.vue";
import { useThemeStore } from "@/store/theme";
import appRouter from "@/routes/AppRouter.js";
import App from "@/app.vue";


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

app.mount("#app");

const themeStore = useThemeStore();
themeStore.applyTheme();
