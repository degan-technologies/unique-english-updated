import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

import "primeicons/primeicons.css";
import "quill/dist/quill.snow.css";
// PrimeVue Styles
import "primeicons/primeicons.css";
import "primevue/resources/primevue.min.css";
import "primevue/resources/themes/saga-blue/theme.css";

// PrimeVue Styles
import "primevue/resources/themes/saga-blue/theme.css"; // Theme of your choice
import "primevue/resources/primevue.min.css"; // Core CSS
import "primeicons/primeicons.css"; // Icons CSS
import "primevue/resources/themes/saga-blue/theme.css";

// Quill Editor Styles
import "quill/dist/quill.snow.css";

import ThemeSwitcher from "@/components/ThemeSwitcher.vue";
import { useThemeStore } from "@/store/theme";
import appRouter from "@/routes/AppRouter.js";
import App from "@/app.vue";

const app = createApp(App);

app.use(createPinia());
app.use(appRouter);
app.use(PrimeVue);
app.use(Toast, {
    // Optional: Add any plugin options here
    timeout: 3000,
    position: "top-right",
});

app.component("theme-switcher", ThemeSwitcher);

app.mount("#app");

// Apply theme on app load
const themeStore = useThemeStore();
themeStore.applyTheme();
