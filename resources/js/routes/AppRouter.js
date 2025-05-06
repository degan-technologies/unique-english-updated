import { createRouter, createWebHashHistory } from "vue-router";

import StudentHome from "@/pages/Student/StudentHome.vue";
import InstructorHome from "@/pages/Instructor/InstructorHome.vue";
import InvoicePage from "@/components/payment/InvoicePage.vue";
import CourseCard from "../components/Course/CourseCard.vue";

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
    ],
});

export default appRouter;
