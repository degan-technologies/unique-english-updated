G N, [28/05/2025 12:29]
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
    <div class="min-h-screen flex items-center mx-auto justify-center px-6 py-20">
        <div class="flex flex-col-reverse md:flex-row items-center justify-between w-[95%] mx-auto gap-8 lg:gap-16">
            <!-- Left Section -->
            <div class="flex-1 text-center md:text-left space-y-6">
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold leading-tight text-gray-800">
                    Join Our <br />
                    <span class="bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">
                        Live Video Sessions
                    </span>
                </h1>

                <p class="text-lg md:text-xl text-gray-600 max-w-xl">
                    Experience real-time learning, engaging discussions, and
                    collaborative sessions — all from the comfort of your space.
                </p>

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

                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="changeTab"
                        class="relative overflow-hidden group inline-flex items-center bg-gradient-to-r from-lime-500 to-emerald-600 hover:from-lime-600 hover:to-emerald-700 text-white font-semibold text-lg p-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl">
                        <span class="relative z-10 flex items-center gap-2"> 
                            Get Plans
                        </span>
                        <span
                            class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-lime-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    </button>
                </div>                
            </div>
 
            <!-- Right Section (Image) -->
            <div class="flex-1 flex justify-center">
                <div class="relative">
                    <div
                        class="absolute -inset-4 bg-gradient-to-tr from-lime-200 to-emerald-300 rounded-2xl blur-lg opacity-30 group-hover:opacity-50 transition duration-300">
                    </div>
                    <div class="relative bg-white p-1 rounded-2xl shadow-2xl transition-transform duration-300">
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