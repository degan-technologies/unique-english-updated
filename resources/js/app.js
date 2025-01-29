import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";

// PrimeVue Styles
import "primevue/resources/themes/saga-blue/theme.css"; // Theme of your choice
import "primevue/resources/primevue.min.css"; // Core CSS
import "primeicons/primeicons.css"; // Icons CSS

// Quill Editor Styles
import "primevue/resources/themes/saga-blue/theme.css"; // PrimeVue Theme
import "quill/dist/quill.snow.css"; // Text editor styles

import appRouter from "@/routes/AppRouter.js";
import App from "@/App.vue";

const app = createApp(App);

app.use(createPinia());
app.use(appRouter);
app.use(PrimeVue);
app.mount("#app");
