import { createRouter, createWebHashHistory } from "vue-router";

import Error from "@/components/Layout/Error.vue";
import InvoicePage from "@/components/payment/InvoicePage.vue";
import InstructorHome from "@/pages/Instructor/InstructorHome.vue";
import StudentHome from "@/pages/Student/StudentHome.vue";

const appRouter = createRouter({
    history: createWebHashHistory("/"),
    routes: [
        // Student page
        { path: "/", name: "student", component: StudentHome },

        // Blog routes
        { path: "/blog", name: "blogList", component: StudentHome },
        { path: "/blog/:slug", name: "blogDetail", component: StudentHome },

        // Instructor page
        { path: "/instructor", name: "instructor", component: InstructorHome },

        {
            path: "/invoice-page/:id",
            name: "InvoicePage",
            component: InvoicePage,
        },

        { path: "/:pathMatch(.*)*", name: "Error", component: Error },
    ],
});

export default appRouter;
