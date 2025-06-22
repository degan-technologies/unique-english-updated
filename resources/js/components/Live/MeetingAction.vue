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
                <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">
                    Join Our <br />
                    <span class="text-3xl  bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">
                        Live Video Sessions
                    </span>
                </h1>

                <p class="text-lg md:text-xl text-gray-600 max-w-xl">
                    Experience real-time learning, engaging discussions, and
                    collaborative sessions — all from the comfort of your space.
                </p>
                 
                 <button @click="changeTab"
                        class="bg-lime-700 text-white px-4 py-2 rounded-md hover:bg-lime-800 transition">
                        Get Plans
                    </button>

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
            </div>
            <!-- Right Section (Image) -->
            <div class="flex-1 flex justify-center">
                <div class="relative"> 
                    <div class="relative bg-white p-1 rounded-2xl  transition-transform duration-300">
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