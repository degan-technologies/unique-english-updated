<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/useAppStore";

const appStore = useAppStore();
const { isLoggedIn,logoImage, authUser } = storeToRefs(appStore);

import Login from '@/components/Auth/Login.vue';
import Body from "@/components/Body.vue";
import Header from "@/components/Layout/Header.vue";
import Footer from "@/components/Layout/Footer.vue";
import AboutUs from "@/pages/common/AboutUs.vue";
import CourseCard from "@/components/Course/CourseCard.vue";
import LiveSession from "./components/Course/LiveSession.vue";
import CourseDetail from "./components/Course/CourseDetail.vue";
import VideoPlayer from "./components/Course/VideoPlayer.vue";
import Quize from "./components/Course/Quize.vue";
const appStore = useAppStore();
const { isLoggedIn, authUser } = storeToRefs(appStore);

onMounted(() => {
    appStore.fetchUserInfo();
});
</script>

<template>
    <div class="flex-grow flex">
        <div class="flex-grow p-4 mt-6">
            <template v-if="isLoggedIn">
                <Body />
            </template>
            <template v-else>
                <Login />
            </template>
        </div>
        <!-- About Us Section -->

        <!-- Footer Section -->
        <Footer />
    </div>
</template>

<style>
/* Ensure components don't overlap */
body {
    margin: 0;
    padding: 0;
}
</style>
