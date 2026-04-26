<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";

import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const studentStore = UseStudentStore();
const { TestTab, liveSchedulTab } = storeToRefs(studentStore);

const appStore = useAppStore();
const { hero, selectedComponentId } = storeToRefs(appStore);

const router = useRouter();
const displayedText = ref("");

const activeTab = ref(null);
const showDemoModal = ref(false);

const typingSpeed = 150;
const erasingSpeed = 100;
const delayBetweenWords = 2000;
let wordIndex = 0;
let charIndex = 0;
let isErasing = false;
const words = [
    "Vocabulary",
    "Grammar",
    "Speaking",
    "Writing",
    "Listening",
    "Reading",
]; 

function changeTab() {
    router.push({
        name: "student",
        query: {
            tab: TestTab.value,
        },
    });
}

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: "smooth" });
};

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

watch(
    () => selectedComponentId.value,
    () => {
        if (selectedComponentId.value) {
            scrollToSection(selectedComponentId.value);
            selectedComponentId.value = null;
        }
    },
    { immediate: true }
); 

onMounted(() => {
    type(); 
});
</script>

<template>
    <section id="hero"
        class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 flex items-center py-20 lg:py-24 font-['Inter',sans-serif] overflow-hidden">
        <div class="container mx-auto px-4 sm:px-6 lg:px-12">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-20">
                <div class="lg:w-1/2 text-center lg:text-left space-y-6 order-2 lg:order-1">
                    <h1
                        class="text-2xl sm:text-3xl lg:text-4xl pt-8 xl:text-5xl font-black font-serif leading-[1.1] tracking-tight">
                        <span class="text-gray-900">
                            {{ hero?.title || "Master English with" }}
                        </span>
                        <br />
                        <span class="bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">
                           With Unique
                        </span>
                    </h1>

                    <p class="text-gray-600 text-base md:text-lg lg:text-xl leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        {{
                            hero?.description ||
                            "Join thousands of learners worldwide. Access premium courses, interactive books, and live sessions with certified instructors."
                        }}
                    </p>
                    <p class="text-base md:text-lg lg:text-xl leading-relaxed max-w-2xl mx-auto lg:mx-0 text-lime-500">
                        {{ displayedText }}<span class="cursor">|</span>
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-2">
                        <button @click="changeTab"
                            class="bg-gradient-to-r from-lime-500 to-lime-600 text-white font-bold px-8 py-3.5 rounded-lg hover:shadow-xl hover:scale-105 transition-all inline-flex items-center gap-2">
                           Test your English Skills
                            <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button> 
                    </div>
                </div>

                <!-- RIGHT IMAGE -->
                <div class="lg:w-1/2 order-1 lg:order-2">
                    <div class="rounded-3xl shadow-2xl overflow-hidden bg-white border border-gray-100">
                        <img :src="hero?.banner" alt="Hero Banner" class="w-full h-auto object-cover rounded-2xl" />
                    </div>
                </div>
            </div>
        </div>
    </section>
 
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap");

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
