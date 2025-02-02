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

import App from "@/App.vue";
import appRouter from "@/routes/AppRouter.js";

const app = createApp(App);


app.use(createPinia());
app.use(appRouter);
app.use(PrimeVue);
app.use(Vue3Toasity, {
    autoClose: 3000,
});
app.mount("#app");
