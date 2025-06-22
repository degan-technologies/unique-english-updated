<script setup>
import { storeToRefs } from "pinia";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const cartStore = useCartStore();
const studentStore = UseStudentStore();

const { courseDetailTab, videoPlayerTab, courses, selectedCourseSlug } =
    storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

const router = useRouter();

const words = ["Vocabulary", "Grammar", "Speaking", "Writing", "Listening", "Reading"];
const displayedText = ref("");
const typingSpeed = 150;
const erasingSpeed = 100;
const delayBetweenWords = 2000;
let wordIndex = 0;
let charIndex = 0;
let isErasing = false;

function type() {
    if (!isErasing) {
        if (charIndex < words[wordIndex].length) {
            displayedText.value += words[wordIndex][charIndex];
            charIndex++;
            setTimeout(type, typingSpeed);
        } else {
            setTimeout(() => {
                isErasing = true;
                type();
            }, delayBetweenWords);
        }
    } else {
        if (charIndex > 0) {
            displayedText.value = displayedText.value.slice(0, -1);
            charIndex--;
            setTimeout(type, erasingSpeed);
        } else {
            isErasing = false;
            wordIndex = (wordIndex + 1) % words.length;
            setTimeout(type, typingSpeed);
        }
    }
}

function addItems(item) {
    let selectedItem = {
        type: "course",
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.thumbnail_url,
    };
    cartStore.addToCart(selectedItem);
}

function changeTab(slug) {
    router.push({
        name: "student",
        query: {
            tab: courseDetailTab.value,
            slug: slug,
        },
    });

    selectedCourseSlug.value = slug;
}

onMounted(() => {
    type();
    studentStore.fetchCourses();
});
</script>

<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen relative" id="courses">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">
            Popular Courses
        </h1>
        <div class="text-center mb-4">
            <h1 class="typing-text text-xl sm:text-2xl font-semibold text-lime-700">
                {{ displayedText }}
                <span class="cursor">|</span>
            </h1>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative">
            <div v-for="course in courses" :key="course.id"
                class="relative bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 cursor-pointer group">
                <div class="relative group">
                    <img :src="course?.thumbnail_url" :alt="course.course_name"
                        class="w-full h-40 object-cover rounded-t-lg" />
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-t-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <button class="bg-lime-500 rounded-full p-3 shadow-md text-white hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5.25 5.25l13.5 6.75-13.5 6.75V5.25z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="p-4 flex flex-col">
                    <div class="h-20">
                        <h2 class="text-lg font-bold mb-2 line-clamp-1">
                            {{ course.course_name }}
                        </h2>
                        <div class="flex flex-1 justify-between">
                            <p class="text-sm mt-1 text-gray-600">
                                By: {{ course.user.first_name }} {{ course.user.middle_name }}
                            </p>
                            <div class="flex items-center gap-2 font-bold">
                                <span class="text-yellow-500 text-lg">&#9733;</span>
                                <span class="ml-1 text-md text-gray-600">{{
                                    course.averageRating
                                    }}</span>
                            </div>
                        </div>
                    </div> 
                    <div class="h-fit bottem-0">
                        <div class="text-lg font-semibold flex flex-row items-center justify-between">
                            <p class="self-center my-auto">
                                {{ course.price?.toFixed(2) }} ETB
                            </p>
                            <button v-if="!course.isMyCourse" @click="addItems(course)"
                                class="ml-auto focus:outline-none">
                                <i class="fa-solid fa-cart-plus right-8 text-lime-700 p-3 hover:text-lime-700"></i>
                            </button>
                            <button v-else
                                class="text-lime-700 font-normal h-fit w-fit text-sm px-2 py-1 rounded-md mt-3 border-2 border-yellow-400 hover:border-yellow-600">
                                Paid
                            </button>
                        </div>

                        <button @click="changeTab(course.slug)"
                            class="mt-4 border border-lime-700 text-lime-800 w-full px-4 py-2 rounded hover:bg-lime-800 hover:text-white focus:outline-none transition-colors font-medium text-lg">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
