<script setup>
import { storeToRefs } from "pinia";
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import Test from "../Course/Test.vue";

import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const studentStore = UseStudentStore();
const { TestTab } = storeToRefs(studentStore);

const appStore = useAppStore();
const { hero, exploreCourses, selectedComponentId } = storeToRefs(appStore);

const activeTab = ref(null);
const router = useRouter();
const imageLoaded = ref(false);

function changeTab() {
    router.push({
        name: "student",
        query: {
            tab: TestTab.value,
        },
    });

    selectedCourseSlug.value = slug;
}

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: "smooth" });
    }
};

const handleImageLoad = () => {
    imageLoaded.value = true;
};

watch(
    () => selectedComponentId.value,
    () => {
        scrollToSection(selectedComponentId.value);
        selectedComponentId.value = null;
    },
    { immediate: true },
);
</script>

<template>
    <section
        class="relative min-h-screen bg-gradient-to-br from-gray-50 via-white to-lime-50"
        id="hero"
    >
        <div class="relative container mx-auto px-6 py-16 lg:py-24">
            <div
                class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center"
            >
                <!-- Content Section -->
                <div class="space-y-8 lg:pr-8">
                    <!-- Main Heading -->
                    <div class="space-y-6">
                        <h1
                            class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight text-gray-900"
                        >
                            <span class="block">Learn without limits,</span>
                            <span class="block">Anytime, Anywhere</span>
                        </h1>

                        <p
                            class="text-lg md:text-xl text-gray-600 leading-relaxed max-w-2xl"
                        >
                            {{
                                hero?.description ||
                                "Empower Your future with world-class courses, expert instructors, and flexible learning experience tailored to your needs."
                            }}
                        </p>
                    </div>

                    <!-- Call-to-Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <button
                            @click="changeTab()"
                            class="inline-flex items-center justify-center px-8 py-4 bg-lime-500 text-white font-semibold rounded-lg hover:bg-lime-600 transition-colors duration-200 shadow-lg"
                        >
                            <span class="mr-2">Test Your Level</span>
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                ></path>
                            </svg>
                        </button>

                        <button
                            @click="scrollToSection('courses')"
                            class="inline-flex items-center justify-center px-8 py-4 bg-slate text-gray-800 font-semibold rounded-lg border-2 border-gray-200 hover:border-lime-300 hover:bg-lime-50 transition-colors duration-200"
                        >
                            <span class="mr-2">Explore Courses</span>
                        </button>
                    </div>
                </div>

                <!-- Image Section -->
                <div class="relative lg:h-auto">
                    <div class="relative rounded-3xl overflow-hidden p-8">
                        <!-- Image Skeleton -->
                        <div
                            v-if="!imageLoaded"
                            class="w-full h-64 md:h-80 lg:h-96 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-pulse rounded-2xl flex items-center justify-center"
                        >
                            <div class="text-center text-gray-500">
                                <svg
                                    class="w-12 h-12 mx-auto mb-4 opacity-50"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Actual Image -->
                        <img
                            :src="hero?.banner || '/images/default-hero.jpg'"
                            alt="Learning Platform"
                            :class="[
                                'w-full h-auto object-cover rounded-2xl transition-opacity duration-300',
                                imageLoaded
                                    ? 'opacity-100'
                                    : 'opacity-0 absolute inset-0',
                            ]"
                            @load="handleImageLoad"
                            @error="imageLoaded = true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabs Content -->
    <div v-if="activeTab === 0">
        <Test />
    </div>
    <div v-if="activeTab === 1"></div>
</template>

<style scoped>
/* Simple transitions only */
* {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Button hover effects */
button {
    position: relative;
    overflow: hidden;
}

button::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.15),
        transparent
    );
    transition: left 0.6s ease-in-out;
}

button:hover::before {
    left: 100%;
}

/* Skeleton animation */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.animate-pulse {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}
</style>
