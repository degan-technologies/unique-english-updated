<script setup>
import { storeToRefs } from "pinia";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { UseStudentStore } from "@/store/UseStudentStore";
import Hero from "@/components/Layout/Hero.vue";
import Header from "@/components/Layout/Header.vue";
import CourseCard from "@/components/Course/CourseCard.vue";
import CourseDetail from "@/components/Course/CourseDetail.vue";
import VideoPlayer from "@/components/Course/VideoPlayer.vue";
import Footer from "@/components/Layout/Footer.vue";
import AboutUs from "@/pages/common/AboutUs.vue";
import LiveStreamingVue from "@/components/Live/LiveStreaming.vue";
import Book from "@/components/Book/Book.vue";
import BookDetails from "@/components/Book/BookDetails.vue";
import Pdf from "@/components/Book/Pdf.vue";
import Schedule from "@/components/Live/Schedule.vue";
import QuizReader from "@/components/Course/QuizReader.vue";
import AddSchedule from "@/components/Live/AddSchedule.vue";
import ManageSchedule from "@/components/Live/ManageSchedule.vue";
import ManagePlan from "@/components/Live/ManagePlan.vue";
import AddPlan from "@/components/Live/AddPlan.vue";

const studentStore = UseStudentStore();
const {
    liveSchedulTab,
    landingPageTab,
    courseDetailTab,
    videoPlayerTab,
    bookOverviewTab,
    bookReadingTab,
} = storeToRefs(studentStore);

const route = useRoute();
const router = useRouter();
const currentTab = ref({
    tab: route.query.tab,
    slug: route.query.slug,
});

watch(
    () => route.query.tab,
    () => {
        currentTab.value = {
            tab: route?.query.tab,
            slug: route?.query.slug,
        };
    }
);
</script>

<template>
    <div class="flex flex-col min-h-screen bg-gray-50 text-gray-800">
        <div class="sticky top-0 z-50 shadow bg-white w-full">
            <Header />
        </div>
        <div class="h-screen overflow-y-auto scrollbar">
            <div class="w-full mt-18">
                <div
                    v-if="currentTab.tab == courseDetailTab && currentTab.slug"
                >
                    <div>
                        <CourseDetail />
                    </div>
                </div>

                <div v-if="currentTab.tab == videoPlayerTab && currentTab.slug">
                    <VideoPlayer />
                </div>
                <div
                    v-if="currentTab.tab == bookOverviewTab && currentTab.slug"
                >
                    <BookDetails />
                </div>
                <div v-if="currentTab.tab == bookReadingTab && currentTab.slug">
                    <Pdf pdfUrl="/images/req.pdf" />
                </div>
                <div v-if="currentTab.tab == liveSchedulTab">
                    <Schedule />
                </div>
                <div v-if="currentTab.tab == clandingPageTab">
                    <Hero class="w-full mb-10" />
                    <CourseCard @mousemove="togglehover" />
                    <LiveStreamingVue />
                    <Book />
                    <AboutUs />
                </div>

                <div class="mb-10">
                    <AddSchedule />
                </div>

                <div class="mb-10">
                    <Schedule />
                </div>

                <div>
                    <ManageSchedule />
                </div>

                <div class="mb-10">
                    <AddPlan />
                </div>

                <div class="w-full">
                    <ManagePlan />
                </div>

                <div class="w-full bg-gray-100">
                    <QuizReader />
                </div>

                <div class="w-full bg-gray-900 text-white">
                    <Footer />
                </div>
            </div>
        </div>
    </div>
</template>
