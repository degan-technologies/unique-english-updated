import { createRouter, createWebHashHistory } from "vue-router";

import StudentHome from "@/pages/Student/StudentHome.vue";
import InstructorHome from "@/pages/Instructor/InstructorHome.vue";
import Login from "@/components/Auth/Login.vue";
import RegisterForm from "../components/Auth/RegisterForm.vue";

import InvoicePage from "@/components/payment/InvoicePage.vue";
import MyCourse from "@/components/Course/EnrolledManagement.vue";
const appRouter = createRouter({
    history: createWebHashHistory("/"),
    routes: [
        // Student page
        { path: "/", name: "student", component: StudentHome },

        // Instructor page
        { path: "/instructor", name: "instructor", component: InstructorHome },

        // Login page
        { path: "/login", name:"login", component: Login },

        // Registration page
        { path: "/register", name: "register", component: RegisterForm },

        // OTP verification page
        { path: "/otp-verification", name: "otp-verification", component: RegisterForm },

        { path: "/invoice-page/:id", name: "InvoicePage", component: InvoicePage },
        {path: "/my-course", component: MyCourse, name: "my-course"}
    ],
});

export default appRouter;