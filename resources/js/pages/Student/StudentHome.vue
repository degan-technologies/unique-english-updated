<script setup>
import { storeToRefs } from "pinia";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import { UseStudentStore } from "@/store/UseStudentStore";

import Pdf from "@/components/Book/Pdf.vue";
import Book from "@/components/Book/Book.vue";
import Hero from "@/components/Layout/Hero.vue";
import AboutUs from "@/pages/common/AboutUs.vue";
import Header from "@/components/Layout/Header.vue";
import Footer from "@/components/Layout/Footer.vue"; 
import BookDetails from "@/components/Book/BookDetails.vue";
import CourseCard from "@/components/Course/CourseCard.vue";
import VideoPlayer from "@/components/Course/VideoPlayer.vue";
import CourseDetail from "@/components/Course/CourseDetail.vue";
import LiveStreamingVue from "@/components/Live/LiveStreaming.vue";
import MyCourse from "@/components/Course/EnrolledManagement.vue";
import MeetingAction from "@/components/Live/MeetingAction.vue";
import WhatExpect from "@/components/Layout/WhatExpect.vue";
import Test from "@/components/Course/Test.vue";
import ProfileForm from "@/components/Profile/ProfileForm.vue";
import YoutubeEmbed from "@/components/Layout/YoutubeEmbed.vue";

const appStore = useAppStore();
const AuthStore = useAuthStore();
const studentStore = UseStudentStore();

const { isLoggedIn } = storeToRefs(appStore);
const { showLoginForm } = storeToRefs(AuthStore);
const {
    liveSchedulTab,
    landingPageTab,
    courseDetailTab,
    videoPlayerTab,
    bookOverviewTab,
    bookReadingTab,
    myCourseTab,
    TestTab,
    courses,
    profile,
    selectedCourseSlug,
} = storeToRefs(studentStore);

const route = useRoute(); 
const router = useRouter();

const currentTab = ref({
    tab: route.query.tab,
    slug: route.query.slug,
});
 
const profileTab = computed(() => route.query.currentTab);

selectedCourseSlug.value = route.query.slug;

const selectedCourse = computed(() => {
    if (Array.isArray(courses.value)) {
        return courses.value.find((item) => item.slug === selectedCourseSlug.value) || null;
    }
    return null;
});

function redirectToLogin() { 
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        router.push('/'); 
    }
}

watch(
    () => route.query.tab,
    async (newTab) => {
        if (newTab === bookReadingTab.value) { 
            await new Promise((resolve) => setTimeout(resolve, 1000)); 
        }

        currentTab.value = {
            tab: route?.query.tab,
            slug: route?.query.slug,
        };

        await new Promise((resolve) => setTimeout(resolve, 1000)); 
    },
    { immediate: true }
);
</script>

<template>
    <div class="flex flex-col min-h-screen bg-gray-50 text-gray-800">
        <div class="sticky top-0 z-50 shadow bg-white w-full">
            <Header />
        </div> 
 
        <!-- Main Content -->
        <div  class="flex-grow overflow-y-auto scrollbar">
            <div class="w-full mt-18 min-h-[calc(100vh-144px)]">
                <div v-if="currentTab.tab == courseDetailTab && currentTab.slug">
                    <CourseDetail />
                </div>

                <div v-else-if="currentTab.tab == videoPlayerTab && currentTab.slug"> 
                    {{ redirectToLogin() }}
                    <VideoPlayer />
                </div>

                <div v-else-if="currentTab.tab == myCourseTab">
                    {{ redirectToLogin() }}
                    <MyCourse />
                </div>

                <div v-else-if="
                    currentTab.tab == bookOverviewTab && currentTab.slug
                ">
                    <BookDetails />
                </div>

                <div v-else-if="
                    currentTab.tab == bookReadingTab && currentTab.slug
                ">
                    <Pdf />
                </div>

                <div v-else-if="currentTab.tab == liveSchedulTab">
                    <LiveStreamingVue />
                </div>
                <div class="md:w-[80%] mx-auto my-24" v-if="profileTab === profile">
                    <ProfileForm />
                </div>

                <div v-else-if="currentTab.tab == landingPageTab">
                    <Hero class="w-full mb-10" />
                    <CourseCard />
                    <MeetingAction />
                    <Book />
                    <YoutubeEmbed />
                    <WhatExpect />
                    <AboutUs />
                </div>

                <div v-else-if="currentTab.tab == TestTab">
                    <Test />
                </div>
            </div>
        </div>

        <div class="w-full bg-gray-900 text-white">
            <Footer />
        </div>
    </div>
</template>
