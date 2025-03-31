<script setup>
    import Axios from "axios";
    import { ref, onMounted } from "vue";
    import { useRouter } from "vue-router";
    import Book from "@/components/Book/Book.vue"; 

    import { storeToRefs } from "pinia";
    import { UseStudentStore } from "@/store/UseStudentStore";

    const studentStore = UseStudentStore();
    const { bookReadingTab } = storeToRefs(studentStore);

    const myBooks = ref([]);
    const router = useRouter();

    function changeTab(slug) {
        router.push({
            name: "student",
            query: {
                tab: bookReadingTab.value,
                slug: slug
            },
        });
    }

    function getMycourses() {
        Axios
            .get("/api/courses/my-books")
            .then(res => {
                myBooks.value = res.data.data;
            })
    }

    onMounted(() => {
        getMycourses();
    });
</script>

<template>
    <div>
        <div class="min-h-screen overflow-y-auto">

            <!-- Main Content -->
            <main class="pt-10">
                <div class="container mx-auto p-8 max-w-6xl">
                    <div v-if="myBooks?.length === 0" 
                        class="text-center text-gray-500">
                        No transactions found.
                    </div>
                    <div v-else class="flex flex-col md:flex-row gap-4">
                        <div class="md:w-2/3 space-y-6">
                            <div v-for="myBook in myBooks" :key="myBook.id"
                                class="bg-white border border-gray-300 shadow-lg rounded-lg h-56 overflow-hidden p-4">
                                <div class="flex flex-col md:flex-row">
                                    <div class="md:w-1/2">

                                        <div 
                                        class="relative w-full h-56">
                                        <video ref="videoPlayer"
                                            id="videoPlayer"
                                            class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border"
                                            controls
                                            :poster="myBook?.cover_page_url"
                                            preload="auto">
                                            <source :src="myBook?.intro_video_url"
                                                type="video/mp4" />
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>

                                    </div>
                                    <div class="md:w-1/2 p-4 flex flex-col justify-center">
                                        <h2 class="text-xl font-semibold pb-2">
                                            {{ myBook?.title}}
                                        </h2>
                                        <div class="text-gray-600 text-sm">
                                            <p class="line-clamp">
                                                {{ myBook?.description }}
                                            </p>
                                        </div> 

                                        <!-- Continue Button -->
                                        <button @click="changeTab(myBook?.slug)"
                                            class="mt-9 px-3 py-2 w-28 border border-lime-700 bg-white text-lime-600  font-semibold text-sm rounded-md hover:bg-lime-700">
                                            Continue
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="mt-8">
                    <Book />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
html,
body {
    overflow-y: auto;
    height: auto;
}

.line-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    /* Restrict to 2 lines */
    line-clamp: 2;
    /* Restrict to 2 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
