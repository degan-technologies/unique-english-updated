import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";

import appRouter from "@/routes/AppRouter.js";
import App from "./app.vue";

const app = createApp(App);

app.use(createPinia());
app.use(appRouter);
app.mount("#app");
