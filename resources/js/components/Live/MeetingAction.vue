<script setup>
import { ref } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { UseStudentStore } from "@/store/UseStudentStore";

const router = useRouter();
const studentStore = UseStudentStore();
const image = ref("/images/meeting.png");

const { liveSchedulTab } = storeToRefs(studentStore);

function changeTab() {
    router.push({
        name: "student",
        query: {
            tab: liveSchedulTab.value,
        },
    });
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center px-6 py-20" id="live">
        <div class="flex flex-col-reverse md:flex-row items-center justify-between w-full max-w-7xl gap-8 lg:gap-16">
            <!-- Left Section -->
            <div class="flex-1 text-center md:text-left space-y-6">
                <div class="block text-center mb-12">
                    <h1
                        class="mx-auto text-center text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black font-serif leading-[1.1] tracking-tight max-w-4xl">
                        <span class="text-gray-900"> Join Our </span><br class="my-4" />
                        <span class="bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">Live
                            Video Sessions</span>
                    </h1>

                    <p
                        class="mx-auto text-center text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                        Experience real-time learning, engaging discussions, and
                        collaborative sessions — all from the comfort of your space.
                    </p>
                </div> 

                <div class="pt-4 flex items-center justify-center md:justify-start gap-4 text-gray-500">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-users text-lime-500"></i>
                        <span>Interactive</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-lime-500"></i>
                        <span>Flexible Timing</span>
                    </div>
                </div>
                <button @click="changeTab"
                    class="group bg-gradient-to-r from-lime-500 to-lime-600 hover:from-lime-600 hover:to-lime-700 text-black font-bold px-8 py-4 rounded-xl transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl inline-flex items-center gap-3">
                    <span>Get Live Packages</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
            <!-- Right Section (Image) -->
            <div class="flex-1 flex justify-center">
                <div class="relative">
                    <div class="relative bg-white p-1 rounded-2xl transition-transform duration-300">
                        <img :src="image" alt="Join Live Session"
                            class="w-full max-w-md h-auto object-cover rounded-xl" />
                        <div class="absolute -bottom-5 -right-5 bg-white p-3 rounded-full shadow-lg">
                            <div class="bg-lime-500 text-white p-3 rounded-full animate-pulse">
                                <i class="fa-solid fa-video text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
