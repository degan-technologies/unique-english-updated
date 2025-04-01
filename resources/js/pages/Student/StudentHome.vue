<script setup>
    import { storeToRefs } from "pinia";
    import { computed, onMounted, ref, watch } from "vue";
    import { useRoute, useRouter } from "vue-router";

    import { useAppStore } from '@/store/useAppStore';
    import { useAuthStore } from '@/store/useAuthStore';
    import { UseStudentStore } from "@/store/UseStudentStore";

    import Pdf from "@/components/Book/Pdf.vue";
    import Book from "@/components/Book/Book.vue";
    import Hero from "@/components/Layout/Hero.vue";
    import AboutUs from "@/pages/common/AboutUs.vue";
    import Header from "@/components/Layout/Header.vue";
    import Footer from "@/components/Layout/Footer.vue";
    import Schedule from "@/components/Live/Schedule.vue";
    import BookDetails from "@/components/Book/BookDetails.vue";
    import CourseCard from "@/components/Course/CourseCard.vue";
    import VideoPlayer from "@/components/Course/VideoPlayer.vue";
    import CourseDetail from "@/components/Course/CourseDetail.vue";
    import LiveStreamingVue from "@/components/Live/LiveStreaming.vue";
    import MyCourse from "@/components/Course/EnrolledManagement.vue";

    const appStore = useAppStore();
    const AuthStore = useAuthStore();
    const studentStore = UseStudentStore();
    
    const { isLoggedIn } = storeToRefs(appStore);
    const { showLoginForm, } = storeToRefs(AuthStore);
    const {
        liveSchedulTab,
        landingPageTab,
        courseDetailTab,
        videoPlayerTab,
        bookOverviewTab,
        bookReadingTab,
        myCourseTab,

        courses,
        selectedCourseSlug,
    } = storeToRefs(studentStore);

    const route = useRoute();
    const router = useRouter();

    const currentTab = ref({
        tab: route.query.tab,
        slug: route.query.slug,
    });

    selectedCourseSlug.value = route.query.slug;

    const selectedCourse = computed(()=>{
        return courses.value.find(item => item.slug === selectedCourseSlug.value)
    })

    function redirectRoute() {
        if(!selectedCourse.value?.isMyCourse) {
            router.push({
                name: 'student',
                query: {
                    tab: landingPageTab.value, 
                }
            });
        }

        if(!isLoggedIn.value) {
            showLoginForm.value = true;
        }
        return;
    };

    function redirectToLogin() {

        if(!isLoggedIn.value) {
            showLoginForm.value = true;
        }
    }

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

                <div v-else-if="currentTab.tab == videoPlayerTab && currentTab.slug">
                    {{ redirectRoute() }}
                    <VideoPlayer />
                </div>
                <div v-else-if="currentTab.tab == myCourseTab">
                    {{ redirectToLogin() }}
                    <MyCourse />
                </div>
                <div v-else-if="currentTab.tab == bookOverviewTab && currentTab.slug" >
                    <BookDetails />
                </div>
                <div v-else-if="currentTab.tab == bookReadingTab && currentTab.slug">
                    {{ redirectRoute() }}
                    <Pdf/>
                </div>
                <div v-else-if="currentTab.tab == liveSchedulTab">
                    <Schedule />
                </div>
                <div v-else-if="currentTab.tab == clandingPageTab">
                    <Hero class="w-full mb-10" />
                    <CourseCard @mousemove="togglehover" />
                    <LiveStreamingVue />
                    <Book />
                    <QuizReader />
                    <certificate/>
                    <AboutUs />
                </div>

                <div class="w-full bg-gray-900 text-white">
                    <Footer />
                </div>
            </div>
        </div>
    </div>
</template>
