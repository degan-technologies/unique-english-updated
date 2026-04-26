import { createRouter, createWebHashHistory } from "vue-router";

import StudentHome from "@/pages/Student/StudentHome.vue";
import InstructorHome from "@/pages/Instructor/InstructorHome.vue";
import BlogListPage from "@/pages/common/BlogListPage.vue";
import BlogDetailPage from "@/pages/common/BlogDetailPage.vue"; 
import InvoicePage from "@/components/payment/InvoicePage.vue";
import Error from "@/components/Layout/Error.vue";

const appRouter = createRouter({
    history: createWebHashHistory("/"),
    routes: [
        // Student page
        { path: "/", name: "student", component: StudentHome },

        // Instructor page
        { path: "/instructor", name: "instructor", component: InstructorHome },

        {
            path: "/invoice-page/:id",
            name: "InvoicePage",
            component: InvoicePage,
        },
        {
            path: "/blogs",
            name: "blogList",
            component: BlogListPage,
        },
        {
            path: "/blogs/:slug",
            name: "blogDetail",
            component: BlogDetailPage,
        }, 
        { path: "/:pathMatch(.*)*", name: "Error", component: Error },
    ],
});

export default appRouter;
