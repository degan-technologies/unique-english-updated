<script setup>
import Axios from "axios";
import videojs from "video.js";
import "video.js/dist/video-js.css";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { onMounted, ref, watch, nextTick, onBeforeUnmount } from "vue";

import ReviewList from "@/components/Course/ReviewList.vue";
import Spinner from "@/components/Layout/Spinner.vue";


const AuthStore = useAuthStore();
const appStore = useAppStore();

const { isLoggedIn } = storeToRefs(appStore);
const { showLoginForm } = storeToRefs(AuthStore);

const studentStore = UseStudentStore();
const { bookReadingTab, books, selectedbookslug } = storeToRefs(studentStore);

const route = useRoute();
const router = useRouter();

const checkoutUrl = ref(null);
const selectedbook = ref(null);
const isLoading = ref(false);
const startLoading = ref(false);
selectedbookslug.value = route.query.slug;

// Video player related refs
const currentTime = ref(0);
const duration = ref(0);
const bufferProgress = ref(0);
const videoPlayer = ref(null);
const player = ref(null);
const videoSource = ref("");
const isPlaying = ref(false);
const showPdfViewer = ref(false);

// Video player event handlers
const onTimeUpdate = () => {
    if (player.value) {
        currentTime.value = player.value.currentTime();
    }
};

const onLoadedMetadata = () => {
    if (player.value) {
        duration.value = player.value.duration();
    }
};

const onProgress = () => {
    if (player.value && duration.value > 0) {
        const buffered = player.value.buffered();
        if (buffered.length) {
            bufferProgress.value = (buffered.end(0) / duration.value * 100);
        }
    }
};

async function enrollBook(item) {
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }

    isLoading.value = true;

    if (item.isMyBook) {
        try {
            await router.push({
                name: "student",
                query: {
                    tab: bookReadingTab.value,
                    slug: item.slug,
                },
            });
            selectedbookslug.value = item.slug;
        } finally {
            isLoading.value = false;
        }
        return;
    }

    try {
        const selectedItem = [{ type: "book", slug: item.slug }];
        const res = await Axios.post("/api/initiate-payment", {
            cartItems: selectedItem,
        });
        checkoutUrl.value = res.data.checkout_url;
        window.open(checkoutUrl.value, "_blank");
    } catch (error) {
        console.error("Payment initiation error:", error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    startLoading.value = true;
    try {
        if (!selectedbook.value) {
            await studentStore.fetchBooks();
        }

        if (Array.isArray(books.value)) {
            selectedbook.value = books.value.find(
                (item) => item?.slug === selectedbookslug.value
            ) || null;
        }
    } finally {
        startLoading.value = false;
    }
});

watch(
    () => route.query.slug,
    () => {
        selectedbookslug.value = route.query.slug;
        if (Array.isArray(books.value)) {
            selectedbook.value = books.value.find(
                (item) => item?.slug === selectedbookslug.value
            ) || null;
        }
    }
);

function playVideo() {
    if (isPlaying.value || !selectedbook.value?.intro_video_url?.trim()) {
        return;
    }

    isPlaying.value = true;
    videoSource.value = selectedbook.value.intro_video_url;

    nextTick(() => {
        if (player.value) {
            player.value.dispose();
        }

        player.value = videojs(videoPlayer.value, {
            controls: true,
            autoplay: true,
            preload: "auto",
            fluid: true,
            aspectRatio: "16:9",
            responsive: true,
            sources: [{
                src: videoSource.value,
                type: "video/mp4"
            }]
        });

        player.value.on("timeupdate", onTimeUpdate);
        player.value.on("loadedmetadata", onLoadedMetadata);
        player.value.on("progress", onProgress);
        player.value.on("error", (error) => {
            console.error("Video player error:", error);
        });
    });
}

onBeforeUnmount(() => {
    if (player.value) {
        player.value.dispose();
        player.value = null;
    }
});
</script>

<template>
    <div>
        <div v-if="!selectedbook && startLoading" class="flex justify-center items-center h-screen">
            <Spinner />
        </div>

        <div v-else-if="selectedbookslug" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
            <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
                <!-- Left Side: Book Info & Description -->
                <div>
                    <div class="text-left">
                        <h1 class="text-4xl text-gray-900 font-bold">
                            {{ selectedbook?.title }}
                        </h1>
                        <div class="flex my-2 gap-4">
                            <div>
                                <img :src="selectedbook?.user?.profile"
                                    :alt="selectedbook?.user?.first_name"
                                    class="w-12 h-12 object-cover mt-4 rounded-full" />
                            </div>
                            <div class="text-lg text-gray-700 mt-2">
                                <p>Book Offered by</p>
                                <p class="font-bold">
                                    {{ selectedbook?.user?.first_name || 'Unknown Author' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Video/Thumbnail Section -->
                    <div class="w-full aspect-video rounded-lg mt-3 bg-black relative overflow-hidden">
                        <!-- Thumbnail with play icon -->
                        <div v-if="!isPlaying" class="w-full h-full cursor-pointer relative group" @click="playVideo">
                            <img :src="selectedbook?.cover_page_url || '/images/default-book-cover.jpg'"
                                alt="Book Cover"
                                class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90" />

                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                                <div class="relative">
                                    <span class="animate-pulse-circle absolute inset-0"></span>
                                    <div
                                        class="relative z-10 p-4 w-14 h-14 bg-lime-500 rounded-full flex items-center justify-center shadow-lg hover:bg-lime-600 transition-colors">
                                        <i class="fas fa-play text-white text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Video Player -->
                        <div v-else class="w-full h-full">
                            <video ref="videoPlayer" class="video-js vjs-default-skin w-full h-full" playsinline>
                                <source :src="videoSource" type="video/mp4" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>

                    <!-- Book Description -->
                    <div class="bg-white pt-2 py-8 rounded-b-lg mt-4">
                        <div class="text-left mb-6">
                            <h2 class="text-3xl text-slate-600 font-semibold mb-4">
                                Book Description
                            </h2>
                            <div class="prose prose-sm sm:prose-base max-w-none ql-editor preview"
                                v-html="selectedbook?.description || 'No description available'">
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <div v-if="selectedbook" class="mt-4">
                        <ReviewList :feedBacks="selectedbook?.feedBacks" :averageRating="selectedbook?.averageRating"
                            :starDistribution="selectedbook?.starDistribution" :showOnly="!selectedbook?.isMyBook" />
                    </div>
                </div>

                <!-- Right Side: Book Details -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm sticky top-4 h-fit">
                    <button v-if="selectedbook" @click="enrollBook(selectedbook)" :disabled="isLoading"
                        class="mt-2 bg-lime-600 hover:bg-lime-700 text-white px-4 py-3 rounded-lg w-full flex items-center justify-center gap-2 transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="font-medium">
                            {{
                                isLoading
                                    ? "Processing..."
                                    : selectedbook?.isMyBook
                                        ? "Continue Reading"
                                        : `Buy for ${selectedbook?.discount || selectedbook?.price} Birr`
                            }}
                        </span>
                    </button>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-user-check text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Author:</span>
                                <p class="text-gray-600">{{ selectedbook?.auther || 'Unknown' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-language text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Language:</span>
                                <p class="text-gray-600">{{ selectedbook?.language || 'Not specified' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-file-alt text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Format:</span>
                                <p class="text-gray-600">{{ selectedbook?.file_format || 'Unknown' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-calendar-day text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Published:</span>
                                <p class="text-gray-600">{{ selectedbook?.publish_date || 'Unknown date' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-tag text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Original Price:</span>
                                <p class="text-gray-600">{{ selectedbook?.price }} Birr</p>
                            </div>
                        </div>

                        <div v-if="selectedbook?.discount" class="flex items-start">
                            <i class="fas fa-percentage text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Discount Price:</span>
                                <p class="text-gray-600">{{ selectedbook?.discount }} Birr</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-file text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Pages:</span>
                                <p class="text-gray-600">{{ selectedbook?.page_number || 'Unknown' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-book text-lime-600 mt-1 mr-3"></i>
                            <div class="flex flex-row gap-4">
                                <span class="font-semibold text-gray-700">Edition:</span>
                                <p class="text-gray-600">{{ selectedbook?.eddition || '1st' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex justify-center items-center h-screen">
            <div class="text-center">
                <i class="fas fa-book-open text-5xl text-gray-400 mb-4"></i>
                <p class="text-xl text-gray-600">Book not found</p>
                <button @click="router.push('/books')"
                    class="mt-4 bg-lime-600 hover:bg-lime-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Browse Books
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-js {
    width: 100%;
    height: 100%;
    border-radius: 0.5rem;
}

.video-js .vjs-big-play-button {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 68px;
    height: 68px;
    border-radius: 50%;
    border: none;
    background-color: rgba(101, 163, 13, 0.8);
}

.video-js .vjs-big-play-button:hover {
    background-color: rgba(101, 163, 13, 1);
}

@keyframes pulse-circle {
    0% {
        transform: scale(1);
        opacity: 0.7;
        box-shadow: 0 0 5px rgba(132, 204, 22, 0.6);
    }

    50% {
        transform: scale(1.6);
        opacity: 0.4;
        box-shadow: 0 0 20px rgba(91, 150, 9, 0.8);
    }

    100% {
        transform: scale(2);
        opacity: 0;
        box-shadow: 0 0 30px rgba(72, 118, 12, 0.5);
    }
}

.animate-pulse-circle {
    width: 70px;
    height: 70px;
    background-color: rgba(132, 204, 22, 0.5);
    border-radius: 50%;
    animation: pulse-circle 2s infinite ease-out;
    position: absolute;
}

.prose :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

.prose :deep(a) {
    color: #3b82f6;
    text-decoration: underline;
}

.prose :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
}

.prose :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
}
</style>