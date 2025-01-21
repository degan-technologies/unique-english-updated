<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/useAppStore";

// Import components
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
    <div class="w-screen min-h-screen flex flex-col bg-gray-50">
        <!-- Header Section -->
        <Header />
        <!-- Course Card Section with proper spacing -->
        <section class="flex-grow p-6 mt-8">
            <CourseCard />
        </section>

        <div id="app" class="bg-gray-50">
            <CourseDetail />
        </div>

        <div id="app" class="bg-gray-50">
            <VideoPlayer />
        </div>
        <div class="bg-gray-50"><LiveSession /></div>
        <div class="bg-gray-50"><Quize /></div>
        <!-- Main Content Section -->
        <div class="flex-grow flex">
            <div class="flex-grow p-4 mt-6">
                <template v-if="isLoggedIn">
                    <Body />
                </template>
                <template v-else>
                    <AboutUs />
                </template>
            </div>
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
