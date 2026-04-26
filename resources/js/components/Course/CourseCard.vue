<script setup>
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const cartStore = useCartStore();
const studentStore = UseStudentStore();

const { courseDetailTab, videoPlayerTab, courses, selectedCourseSlug } =
    storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

const router = useRouter();

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
    studentStore.fetchCourses();
});
</script>

<template>
    <div
        class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen relative scroll-mt-20"
        id="courses"
    > 
        <div class="block text-center mb-12">
            <h1 class="mx-auto text-center text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black font-serif leading-[1.1] tracking-tight max-w-4xl">
                <span class="text-gray-900">  Our English Courses </span><br class="my-4" />
                <span class="bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">Self-Learning</span>
            </h1>

            <p class="mx-auto text-center text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                Pick up exactly where you left off. Every course is self-paced
            with instructor feedback on speaking and writing tasks.
            </p>
        </div>
        <div
            class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
        >
            <div
                v-for="course in courses"
                :key="course.id"
                class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
            >
                <div class="relative"> 
                    <img
                        :src="course?.thumbnail_url"
                        :alt="course.course_name"
                        class="w-full h-36 object-cover"
                    />
                </div>

                <div class="p-4 flex flex-col min-h-[285px]">
                    <h2
                        class="text-[31px] font-serif text-gray-700 leading-tight mb-2 line-clamp-2 min-h-[56px]"
                    >
                        {{ course.course_name }}
                    </h2>

                    <div class="text-[18px] text-gray-600 line-clamp-2 min-h-[44px]"
                         v-html="course.overview  || 'No description available'">
                    </div>  

                    <div class="mt-auto pt-4">
                        <div
                            class="text-lg font-semibold flex items-center justify-between mb-3"
                            v-if="!course.isMyCourse"
                        >
                            <p class="text-gray-700">{{ course.price?.toFixed(2) }} ETB</p>
                            <button
                                @click="addItems(course)"
                                disabled="course.isMyCourse"
                                class="focus:outline-none text-lime-500 hover:text-lime-700"
                                title="Add to cart"
                            >
                                <span v-if="course?.isMyCourse">paid</span>
                                <i v-else class="fa-solid fa-cart-plus p-1"></i>
                            </button>
                        </div>
                        <button
                            @click="changeTab(course.slug)"
                            class="w-full rounded-full bg-lime-500 text-white py-2.5 px-4 font-semibold hover:bg-lime-700 transition-colors"
                        >
                            {{ course.isMyCourse ? "Continue" : "Get Started" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Keep scroll position aligned with sticky header */
#courses {
    scroll-margin-top: 80px;
}

.typing-text {
    min-height: 1.5rem;
}

.cursor {
    display: inline-block;
    animation: blink 1s steps(2, start) infinite;
}

@keyframes blink {
    to {
        visibility: hidden;
    }
}
</style>
