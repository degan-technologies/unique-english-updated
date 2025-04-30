<script setup> 
import { ref } from "vue";
import { storeToRefs } from "pinia";

import { useRouter } from "vue-router";

import { UseStudentStore } from "@/store/UseStudentStore";

const router = useRouter();
const studentStore = UseStudentStore();
const image = ref('/images/join_live.jpg');

const { liveSchedulTab } = storeToRefs(studentStore);

function changeTab() { 
    router.push({
        name: 'student',
        query: {
            tab: liveSchedulTab.value,
        }
    });
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-white px-6">
        <div class="flex flex-col md:flex-row items-center justify-between w-full max-w-6xl">

            <!-- Left section -->
            <div class="flex-1 mb-12 md:mb-0">
                <h1 class="text-4xl  font-bold text-lime-700 mb-6 leading-tight">
                   Join Our Live Video Session
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                   Experience real-time learning, discussions, and collaboration from the comfort of your home.
                </p>

                <div class="flex flex-col md:flex-row items-center gap-4">
                    <button
                        @click="changeTab()"
                        class="flex items-center gap-2 bg-lime-600 hover:bg-lime-700 text-white font-semibold py-3 px-6 rounded-full transition">
                         Get Plans
                    </button> 
                </div> 
            </div>

            <!-- Right section (image placeholder) -->
            <div class="flex-1 relative flex justify-center">
                <div class="rounded-full bg-blue-100 p-8">
                    <img 
                        :src="image" 
                        alt="Illustration" 
                        class="rounded-full w-64" />
                </div>
            </div>

        </div>
    </div>
</template>