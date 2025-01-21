import { createRouter, createWebHashHistory } from 'vue-router'

import StudentHome from '@/pages/Student/StudentHome.vue';
import InstructorHome from '@/pages/Instructor/InstructorHome.vue';

const appRouter = createRouter({
    history: createWebHashHistory('/'),
    routes: [
        //student page
        { path: '/', name: 'student', component: StudentHome },

        //instructor page
        { path: '/instructor', name: 'instructor', component: InstructorHome },
        ]
})

export default appRouter;
