<script setup>
import { ref } from "vue";
import Hero from "@/components/Layout/Hero.vue";
import Header from "@/components/Layout/Header.vue";
import CourseCard from "@/components/Course/CourseCard.vue";
import CourseDetail from "@/components/Course/CourseDetail.vue";
import LiveSession from "@/components/Course/LiveSession.vue";
import VideoPlayer from "@/components/Course/VideoPlayer.vue";
import Footer from "@/components/Layout/Footer.vue";
import AboutUs from "@/pages/common/AboutUs.vue";
import Error from "../../components/Layout/Error.vue";
import Spinner from "../../components/Layout/Spinner.vue";
</script>

<template>
    <div class="flex flex-col min-h-screen bg-gray-50 text-gray-800">
        <!-- Header Section -->
        <div class="sticky top-0 z-50 shadow bg-white w-full">
            <Header />
        </div>
        <div class="h-screen overflow-y-auto scrollbar">
            <!-- Hero Section -->
            <div class="w-full mb-10">
                <Hero />
            </div>

            <!-- Course Card Section -->
            <div class="w-full">
                <CourseCard />
            </div>

            <!-- Course Detail Section -->
            <div class="w-full">
                <CourseDetail />
            </div>

            <!-- Video Player Section -->
            <div class="w-full">
                <VideoPlayer />
            </div>

            <!-- Live Session Section -->
            <div class="w-full">
                <LiveSession />
            </div>
            <div class="w-full">
                <AboutUs />
            </div>
            <div>
                <Spinner />
            </div>
            <div>
                <Error />
            </div>
            <!-- Footer Section -->
            <div class="w-full bg-gray-900 text-white">
                <Footer />
            </div>
        </div>
    </div>
</template>

<style>
html,
body {
    margin: 0;
    padding: 0;
    width: 100%;
}
</style>
