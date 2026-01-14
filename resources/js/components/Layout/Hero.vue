<script setup>
import { ref, watch } from "vue";
import { storeToRefs } from "pinia";
import Test from "../Course/Test.vue";
import { useRouter } from "vue-router";

import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const studentStore = UseStudentStore();
const { TestTab } = storeToRefs(studentStore);

const appStore = useAppStore();
const { hero, exploreCourses, selectedComponentId } = storeToRefs(appStore);

const activeTab = ref(null);
const router = useRouter();

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

watch(
    () => selectedComponentId.value,
    () => {
        scrollToSection(selectedComponentId.value);
        selectedComponentId.value = null;
    },
    { immediate: true }
);
</script>

<template>
    <section
        class="bg-cover bg-center bg-no-repeat py-16"
        id="hero"
        :style="{ backgroundImage: `url(${hero?.background_image})` }"
    >
        <div
            class="container mx-auto flex flex-col-reverse lg:flex-row items-center px-6"
        >
            <!-- Content Section -->
            <div class="text-center lg:text-left lg:max-w-lg">
                <h1
                    class="text-3xl md:text-4xl font-bold leading-tight text-gray-800"
                >
                    {{ hero?.title }}
                </h1>
                <p class="mt-4 text-gray-800 text-lg">
                    {{ hero?.description }}
                </p>
                <div
                    class="mt-6 flex justify-center lg:justify-start space-x-4"
                >
                    <button
                        @click="changeTab()"
                        class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition"
                    >
                        Test Your Level
                    </button>
                    <button
                        @click="scrollToSection('courses')"
                        class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 transition"
                    >
                        Explore Courses
                    </button>
                </div>
            </div>

            <!-- Image Section -->
            <div
                class="flex justify-center lg:justify-end w-full lg:w-1/2 mt-8 lg:mt-0"
            >
                <img
                    :src="hero?.banner"
                    alt="E-learning Illustration"
                    class="rounded-lg shadow-lg max-w-full"
                />
            </div>
        </div>
    </section>

    <!-- Tabs Content -->
    <div v-if="activeTab === 0">
        <Test />
    </div>
    <div v-if="activeTab === 1"></div>
</template>
