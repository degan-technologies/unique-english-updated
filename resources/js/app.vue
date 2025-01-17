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

const appStore = useAppStore();
const { isLoggedIn, authUser } = storeToRefs(appStore);

onMounted(() => {
    appStore.fetchUserInfo();
});

const courses = [
    {
        id: 1,
        image: "/images/course1.jpg",
        title: "Vue.js 3 - The Complete Guide",
        author: "John Doe",
        rating: 4.5,
        price: 29.99,
    },
    {
        id: 2,
        image: "/images/course2.jpg",
        title: "Mastering JavaScript",
        author: "Jane Smith",
        rating: 4.8,
        price: 19.99,
    },
    {
        id: 3,
        image: "/images/course3.jpg",
        title: "Tailwind CSS for Beginners",
        author: "Alex Johnson",
        rating: 4.7,
        price: 24.99,
    },
    {
        id: 4,
        image: "/images/course4.jpg",
        title: "React.js Crash Course",
        author: "Chris Brown",
        rating: 4.6,
        price: 34.99,
    },
];
</script>

<template>
    <div class="w-screen h-screen flex flex-col gap-4">
        <!-- Header Section -->
        <Header />

        <!-- Popular Courses Section -->
        <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen">
            <h1 class="text-2xl font-bold mb-6 text-center">Popular Courses</h1>

            <!-- Responsive grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <CourseCard
                    v-for="course in courses"
                    :key="course.id"
                    :course="course"
                    class="transform transition-transform duration-300 hover:scale-105"
                />
            </div>
        </div>

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

        <!-- Footer Section -->
        <Footer />
    </div>
</template>

<style scoped>
/* Additional styles for fine-tuning responsiveness */
h1 {
    font-size: 1.5rem; /* Default size for smaller screens */
}

@media (min-width: 640px) {
    h1 {
        font-size: 2rem; /* Larger size for medium screens */
    }
}

@media (min-width: 1024px) {
    h1 {
        font-size: 2.5rem; /* Even larger size for large screens */
    }
}
</style>
