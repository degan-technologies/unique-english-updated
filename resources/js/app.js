import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";

import "vue3-toastify/dist/index.css";
import Vue3Toasity from "vue3-toastify";

import "primeicons/primeicons.css"; 
import "quill/dist/quill.snow.css"; 
import "primevue/resources/primevue.min.css";
import "primevue/resources/themes/saga-blue/theme.css"; 


// PrimeVue Styles
import "primevue/resources/themes/saga-blue/theme.css"; // Theme of your choice
import "primevue/resources/primevue.min.css"; // Core CSS
import "primeicons/primeicons.css"; // Icons CSS

// Quill Editor Styles
import "primevue/resources/themes/saga-blue/theme.css"; // PrimeVue Theme
import "quill/dist/quill.snow.css"; // Text editor styles

import ThemeSwitcher from "@/components/ThemeSwitcher.vue";
import { useThemeStore } from "@/store/theme";

import appRouter from "@/routes/AppRouter.js";
import App from "@/app.vue";
const app = createApp(App);


app.use(createPinia());
app.use(appRouter);
app.use(PrimeVue);
app.use(Vue3Toasity, {
    autoClose: 3000,
});
app.mount("#app");
app.component("theme-switcher", ThemeSwitcher);

// Apply theme on app load
const themeStore = useThemeStore();
themeStore.applyTheme();