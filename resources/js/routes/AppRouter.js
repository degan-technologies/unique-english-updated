import { createRouter, createWebHashHistory } from 'vue-router'

import StudentHome from '@/pages/Student/StudentHome.vue';
import InstructorHome from '@/pages/Instructor/InstructorHome.vue';
import Login from '../components/Auth/Login.vue';

const appRouter = createRouter({
    history: createWebHashHistory('/'),
    routes: [
        //student page
        { path: '/', name: 'student', component: StudentHome },

        //instructor page
        { path: '/instructor', name: 'instructor', component: InstructorHome },

        { path: '/login', component: Login },
        ]
})

export default appRouter;
