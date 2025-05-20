<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Book from "@/components/Book/Book.vue";
import { storeToRefs } from "pinia";
import { UseStudentStore } from "@/store/UseStudentStore";
import Spinner from "../Layout/Spinner.vue";

const studentStore = UseStudentStore();
const { bookReadingTab } = storeToRefs(studentStore);

const myBooks = ref([]);
const isLoading = ref(false); // <-- Spinner state
const router = useRouter();

function changeTab(slug) {
    router.push({
        name: "student",
        query: {
            tab: bookReadingTab.value,
            slug: slug,
        },
    });
}

function getMycourses() {
    isLoading.value = true;
    Axios.get("/api/courses/my-books")
        .then((res) => {
            myBooks.value = res.data.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

onMounted(() => {
    getMycourses();
});
</script>

<template>
    <div class="min-h-screen overflow-y-auto bg-gray-50">
        <main class="py-6">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
                <!-- Spinner centered -->
                <div v-if="isLoading" class="flex items-center justify-center h-[60vh]">
                    <Spinner />
                </div>

                <!-- No books -->
                <div v-else-if="myBooks?.length === 0" class="text-center text-gray-500 py-12">
                    No transactions found.
                </div>

                <!-- Book list -->
                <div v-else class="flex flex-col gap-6">
                    <div v-for="myBook in myBooks" :key="myBook.id"
                        class="bg-white border border-gray-300 shadow-md rounded-lg overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Video -->
                            <div class="w-full md:w-1/2">
                                <div class="relative w-full h-56 md:h-64">
                                    <video ref="videoPlayer" id="videoPlayer"
                                        class="video-js vjs-default-skin w-full h-full object-cover rounded-t-md md:rounded-none md:rounded-l-md"
                                        controls :poster="myBook?.cover_page_url" preload="auto">
                                        <source :src="myBook?.intro_video_url" type="video/mp4" />
                                        Your browser does not support the video
                                        tag.
                                    </video>
                                </div>
                            </div>

                            <!-- Book info -->
                            <div class="w-full md:w-1/2 p-6 flex flex-col justify-center">
                                <h2 class="text-lg sm:text-xl font-semibold mb-2">
                                    {{ myBook?.title }}
                                </h2>

                                <div class="prose prose-sm sm:prose-base max-w-none ql-editor preview line-clamp"
                                    v-html="myBook?.description">
                                </div>

                                <button @click="changeTab(myBook?.slug)"
                                    class="mt-auto px-4 py-2 w-full sm:w-32 border border-lime-700 bg-white text-lime-600 font-semibold text-sm rounded-md hover:bg-lime-700 hover:text-white transition-colors">
                                    Continue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- Book Component -->
        <div class="w-full mt-12 mx-auto">
            <Book />
        </div>
    </div>
</template>

<style scoped>
html,
body {
    overflow-y: auto;
    height: auto;
}

.line-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
