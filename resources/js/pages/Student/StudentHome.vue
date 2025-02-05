<script setup>
import { storeToRefs } from "pinia";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

import { UseStudentStore } from "@/store/UseStudentStore";

    const studentStore = UseStudentStore();
    
    const {courseDetailTab, videoPlayerTab} = storeToRefs(studentStore);

    const route = useRoute();
    const router = useRouter()
    const currentTab = ref({
        tab:route.query.tab, 
        slug:route.query.slug
    })


    import Hero from "@/components/Layout/Hero.vue";
    import Header from "@/components/Layout/Header.vue";
    import CourseCard from "@/components/Course/CourseCard.vue";
    import CourseDetail from "@/components/Course/CourseDetail.vue";
    import VideoPlayer from "@/components/Course/VideoPlayer.vue";
    import Footer from "@/components/Layout/Footer.vue";
    import AboutUs from "@/pages/common/AboutUs.vue";
    import LiveStreamingVue from "@/components/Course/LiveStreaming.vue";

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
                <div v-if="currentTab.tab == courseDetailTab && currentTab.slug">
                    <div >
                        <CourseDetail />
                    </div>
                </div>

                <div v-else-if="currentTab.tab == videoPlayerTab && currentTab.slug">
                    <VideoPlayer />
                </div>
                <div v-else>
                    <Hero class="w-full mb-10"/>
                    <CourseCard @mousemove="togglehover" />
                    <LiveStreamingVue />
                    <AboutUs />
                </div>

                <div class="w-full bg-gray-900 text-white">
                    <Footer />
                </div>
            </div>
        </div>
    </div>
</template>
