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
    <div class="min-h-screen flex items-center justify-center px-6 py-20">
        <div
            class="flex flex-col-reverse md:flex-row items-center justify-between w-full max-w-7xl gap-16"
        >
            <!-- Left Section -->
            <div class="flex-1 text-center md:text-left">
                <h1
                    class="text-3xl md:text-4xl font-bold text-lime-700 leading-snug mb-6"
                >
                    Join Our
                    <span class="text-gray-900">Live Video Session</span>
                </h1>
                <p class="text-lg text-gray-600 mb-8 max-w-xl">
                    Experience real-time learning, engaging discussions, and
                    collaborative sessions — all from the comfort of your space.
                </p>
                <button
                    @click="changeTab"
                    class="inline-flex items-center gap-3 bg-lime-600 hover:bg-lime-700 text-white font-medium text-lg py-3 px-6 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105"
                >
                    <i class="fa-solid fa-calendar-check"></i>
                    Get Plans
                </button>
            </div>

            <!-- Right Section (Image) -->
            <div class="flex-1 flex justify-center">
                <div
                    class="bg-white rounded-full p-6 shadow-2xl border-4 border-lime-100 transition-transform duration-300"
                >
                    <img
                        :src="image"
                        alt="Join Live Session"
                        class="rounded-full w-72 h-72 object-cover shadow-inner"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
