<script setup>
import { onMounted, computed } from "vue";
import { storeToRefs } from "pinia";
import { useSessionStore } from "@/store/useSessionStore";
import { useRoute } from "vue-router";

import StudentHome from "@/pages/Student/StudentHome.vue";
import InstructorHome from "@/pages/Instructor/InstructorHome.vue";

const sessionStore = useSessionStore();
const { authUser, isLoggedIn } = storeToRefs(sessionStore);

const route = useRoute();

const currentComponentName = computed(() => {
    return route.name;
});
</script>

<template>
    <div>
        <div v-if="authUser?.role === 'systemAdmin'">
            <InstructorHome />
        </div>
        <div v-else-if="authUser?.role === 'instructor'">
            <InstructorHome />
        </div>
        <div v-else-if="authUser?.role === 'student'">
            <div
                v-if="
                    currentComponentName === 'InvoicePage' ||
                    currentComponentName === 'Error' ||
                    currentComponentName === 'blogList' ||
                    currentComponentName === 'blogDetail'
                "
            >
                <RouterView />
            </div>
            <div v-else>
                <StudentHome />
            </div>
        </div>
        <div v-else-if="!isLoggedIn">
            <div
                v-if="
                    currentComponentName === 'blogList' ||
                    currentComponentName === 'blogDetail' ||
                    currentComponentName === 'Error'
                "
            >
                <RouterView />
            </div>
            <div v-else>
                <StudentHome />
            </div>
        </div>
    </div>
</template>
