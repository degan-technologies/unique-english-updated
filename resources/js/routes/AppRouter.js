import { createRouter, createWebHashHistory } from 'vue-router'

import Home from '@/components/Home.vue';

const appRouter = createRouter({
    history: createWebHashHistory('/'),
    routes: [
        { path: '/', name: 'home', component: Home },
        ]
})

export default appRouter;
