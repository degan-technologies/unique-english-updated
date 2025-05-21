<script setup>
import { ref, computed, onMounted } from "vue";
import Spinner from "@/components/Layout/Spinner.vue";

const CHANNEL_ID = "UCIKi8o9soQ30AH2xTmAZzjg";
const videos = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const videosPerPage = 8;
const videoPlayer = ref(null);
const activeVideo = ref({});

// Format date
function formatDate(dateString) {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString("en-US", options);
}

// Fetch videos from RSS feed
async function fetchVideosFromRSS() {
    try {
        const RSS_URL = `https://www.youtube.com/feeds/videos.xml?channel_id=${CHANNEL_ID}`;
        const res = await fetch(`https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent(RSS_URL)}`);
        const data = await res.json();

        videos.value = data.items.map(item => ({
            id: { videoId: item.link.split('v=')[1] },
            snippet: {
                title: item.title,
                publishedAt: item.pubDate,
                thumbnails: {
                    medium: { url: item.thumbnail }
                }
            }
        }));
    } catch (err) {
        console.error("Error fetching RSS feed:", err);
        // Fallback to empty array if RSS fails
        videos.value = [];
    }
}
 
async function fetchAllVideos() {
    try { 
        const API_KEY = "AIzaSyCL9LRlBXV1FIBlyeutdLxmm0_GdYI_NkY";
        const url = `https://www.googleapis.com/youtube/v3/search?key=${API_KEY}&channelId=${CHANNEL_ID}&part=snippet,id&order=date&maxResults=50`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.error && data.error.code === 403) { 
            await fetchVideosFromRSS();
        } else if (data.items) {
            videos.value = data.items.filter(i => i.id.kind === "youtube#video");
        }
    } catch (err) {
        console.error("Error fetching videos:", err); 
        await fetchVideosFromRSS();
    } finally {
        loading.value = false;
        if (videos.value.length) activeVideo.value = videos.value[0];
    }
}
 
const totalPages = computed(() => Math.ceil(videos.value.length / videosPerPage));
const endIndex = computed(() => currentPage.value * videosPerPage);
const paginated = computed(() => {
    const start = (currentPage.value - 1) * videosPerPage;
    return videos.value.slice(start, start + videosPerPage);
});

const activeSrc = computed(() => {
    if (!activeVideo.value.id) return "";
    return `https://www.youtube.com/embed/${activeVideo.value.id.videoId}?autoplay=1&rel=0&enablejsapi=1`;
});

// Controls
function nextPage() {
    if (endIndex.value < videos.value.length) currentPage.value++;
}
function prevPage() {
    if (currentPage.value > 1) currentPage.value--;
}
function shuffleVideos() {
    videos.value = [...videos.value].sort(() => Math.random() - 0.5);
    currentPage.value = 1;
    activeVideo.value = videos.value[0];
}
function setActive(video) {
    activeVideo.value = video;
    // Smooth scroll to the video player
    if (videoPlayer.value) {
        videoPlayer.value.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
}

onMounted(fetchAllVideos);
</script>

<template>
    <div class="p-4 md:p-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between my-8">
            <h1 class="text-2xl md:text-3xl font-bold text-lime-600">
                Free Tutorials
            </h1>
            <button @click="shuffleVideos"
                class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-lime-600 text-lime-600 font-medium hover:bg-lime-50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                </svg>
                Shuffle
            </button>
        </div>

        <div v-if="loading" class="text-center py-20 text-gray-500">
            <Spinner />
        </div>

        <div v-else class="space-y-6">
            <!-- Main Active Video with ref for scrolling -->
            <div ref="videoPlayer" class="w-full rounded-xl overflow-hidden transition-all">
                <div class="mx-auto" style="max-width: 800px">
                    <div class="relative rounded-xl overflow-hidden shadow-lg border-2 border-lime-600/20 bg-black">
                        <iframe :src="activeSrc" class="w-full aspect-video" :key="activeVideo?.id?.videoId"
                            allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>
                    </div>
                <div class="text-xl my-8 font-bold  md:text-2xl text-gray-800 px-1">
                    {{ activeVideo?.snippet?.title }}
                </div>
                </div>
            </div>

            <!-- Thumbnails Grid -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                    <div v-for="video in paginated" :key="video.id.videoId" @click="setActive(video)"
                        class="group cursor-pointer transition-all">
                        <div class="relative rounded-lg overflow-hidden shadow-md border-2 transition-all" :class="video.id.videoId === activeVideo?.id?.videoId
                            ? 'border-lime-600 scale-[0.98]'
                            : 'border-transparent group-hover:border-lime-600/30'
                            ">
                            <img :src="video.snippet.thumbnails.medium.url" :alt="video.snippet.title"
                                class="w-full aspect-video object-cover transition-transform group-hover:scale-105" />
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-all"></div>
                            <div v-if="
                                video.id.videoId === activeVideo?.id?.videoId
                            " class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <div class="w-12 h-12 rounded-full bg-lime-600/90 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-medium text-gray-800 line-clamp-2">
                                {{ video.snippet.title }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ formatDate(video.snippet.publishedAt) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between pt-2">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="flex items-center gap-1 px-4 py-2 rounded-full bg-lime-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-lime-700 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Prev
                    </button>
                    <span class="text-sm text-gray-600">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button @click="nextPage" :disabled="endIndex >= videos.length"
                        class="flex items-center gap-1 px-4 py-2 rounded-full bg-lime-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-lime-700 transition-all">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Aspect ratio for thumbnails */
.aspect-video {
    aspect-ratio: 16 / 9;
}

/* Remove default iframe spacing */
iframe {
    display: block;
    margin: 0;
    padding: 0;
    border: none;
}
</style>
