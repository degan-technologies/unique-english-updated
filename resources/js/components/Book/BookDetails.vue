<script setup>
    import Axios from "axios";
    import { storeToRefs } from "pinia";
    import { onMounted, ref, watch } from "vue";
    import { useRoute, useRouter } from "vue-router";
    import { UseStudentStore } from "@/store/UseStudentStore";
    
    import { useAppStore } from '@/store/useAppStore';
    import { useAuthStore } from '@/store/useAuthStore';

    import ReviewList from "@/components/Course/ReviewList.vue";

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
    selectedbookslug.value = route.query.slug; 

    const currentTime = ref(0);
    const duration = ref(0);
    const bufferProgress = ref(0);
    const videoPlayer = ref(null);
    const player = ref(null);
    const videoSource = ref("");
    const isPlaying = ref(false); 

    const showPdfViewer = ref(false);

    const onTimeUpdate = () => {
        if (player.value) {
            currentTime.value = player.value.currentTime();
            console.log("Current time updated:", currentTime.value);
        }
    };

    const onLoadedMetadata = () => {
        if (player.value) {
            duration.value = player.value.duration();
            console.log("Video metadata loaded. Duration:", duration.value);
        }
    };

    const onProgress = () => {
        if (player.value && duration.value > 0) {
            const buffered = player.value.buffered();
            if (buffered.length) {
                const bufferEnd = buffered.end(0);
                bufferProgress.value = (bufferEnd / duration.value) * 100;
                console.log("Buffer progress:", bufferProgress.value);
            }
        }
    };

    function enrollBook(item) {

        if(!isLoggedIn.value) {
            showLoginForm.value = true;
            return;
        }
        
        if (item.isMyBook) {
            router.push({
                name: "student",
                query: {
                    tab: bookReadingTab.value,
                    slug: item.slug,
                },
            });
            selectedbookslug.value = item.slug;
            return;
        }

        const selectedItem = [{ type: "book", slug: item.slug }];
        Axios.post("/api/initiate-payment", { cartItems: selectedItem })
            .then((res) => {
                checkoutUrl.value = res.data.checkout_url;
                window.open(checkoutUrl.value, "_blank");
            })
            .catch((error) => {
                console.error("Payment initiation error:", error);
            });
    }

    onMounted(async () => {
        if(!selectedbook.value){
            await studentStore.fetchBooks();
        }

        selectedbook.value = books.value.find(
            (item) => item?.slug === selectedbookslug.value
        );
    });

    watch(
        () => route.query.slug,
        () => {
            selectedbookslug.value = route.query.slug;
            selectedbook.value = books.value.find(
                (item) => item?.slug === selectedbookslug.value
            );
        }
    );

    function playVideo() {
        if (isPlaying.value) {
            console.log("Video is already playing.");
            return;
        }
        isPlaying.value = true;

        videoSource.value = selectedbook.value.intro_video_url;
        console.log("Selected video source:", videoSource.value);
        if (!videoSource.value || videoSource.value.trim() === "") {
            console.error("Invalid video source!");
            return;
        }
        nextTick(() => {
            if (player.value) {
                console.log("Disposing existing player...");
                player.value.dispose();
                player.value = null;
            }
            console.log("Initializing the video player...");
            player.value = videojs(videoPlayer.value, {
                controls: true,
                autoplay: true,
                preload: "auto",
                fluid: true,
            });

            player.value.src({
                src: videoSource.value,
                type: "video/mp4",
            });
            console.log("Video source set successfully.");
            player.value.on("timeupdate", onTimeUpdate);
            player.value.on("loadedmetadata", onLoadedMetadata);
            player.value.on("progress", onProgress);
            player.value.on("error", (e) => {
                console.error("Video player error:", e);
            });
            player.value.on("seeked", () => {
                console.log("Video seeked to:", player.value.currentTime());
            });
        });
    }
</script>

<template>
    <div>
        <div v-if="selectedbookslug" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
            <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
                <!-- Left Side: Book Info & Description -->
                <div>
                    <div class="text-left">
                        <h1 class="text-4xl text-gray-900 font-bold">
                            {{ selectedbook?.title }}
                        </h1>
                        <div class="flex my-2 gap-4">
                            <div>
                                <img src="/images/course-1.jpg" :alt="selectedbook?.user.first_name"
                                    class="w-12 h-12 object-cover mt-4 rounded-full" />
                            </div>
                            <div class="text-lg text-gray-700 mt-2">
                                <p>Book Offered by</p>
                                <p class="font-bold">{{ selectedbook?.user.first_name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full p-6 flex flex-col items-start relative">
                        <div class="overflow-hidden w-full aspect-video rounded-t-lg mt-3 relative">
                            <!-- Thumbnail with play icon -->
                            <div v-if="!isPlaying" class="relative w-full h-full cursor-pointer" @click="playVideo">
                                <img :src="selectedbook?.cover_page_url" alt="Course Thumbnail"
                                    class="w-full h-full object-cover transition-transform duration-300 rounded-t-lg shadow-lg hover:shadow-xl" />
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div
                                        class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                                        <i class="fas fa-play-circle text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- Video Player with Video.js -->
                            <div v-else class="relative w-full h-full">
                                <video ref="videoPlayer" id="videoPlayer"
                                    class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border"
                                    controls preload="auto" @timeupdate="onTimeUpdate"
                                    @loadedmetadata="onLoadedMetadata" @progress="onProgress">
                                    <source :src="videoSource" type="video/mp4" />
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 py-8 rounded-b-lg">
                        <div class="text-left mb-6">
                            <div class="flex items-left justify-between">
                                <h2 class="text-3xl text-slate-600 font-semibold">
                                    Book Description
                                </h2>
                            </div>
                            <p class="py-4 text-lg leading-9">
                                {{ selectedbook?.description }}
                            </p>
                        </div>
                    </div>

                    <!-- reviewlist  -->

                    <div v-if="selectedbook"  class="mt-4">
                        <ReviewList 
                            :feedBacks="selectedbook?.feedBacks" 
                            :averageRating="selectedbook?.averageRating"
                            :starDistribution="selectedbook?.starDistribution"
                            :showOnly="!selectedbook?.isMyBook" />
                    </div>
                </div>

                <!-- Right Side: Book Details -->
                <div class="w-full border border-e-gray-300 h-1/2 min-h-fit rounded-lg p-6">
                    <button v-if="selectedbook" 
                        @click="enrollBook(selectedbook)"
                        class="mt-6 bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 w-full">
                        {{ selectedbook?.isMyBook ? " Continue Reading" : "Buy now" }}
                    </button>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Author:</span>{{ selectedbook?.auther }}
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Language:</span>{{ selectedbook?.language }}
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">File Format:</span>{{ selectedbook?.file_format }}
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Publish Date:</span>{{ selectedbook?.publish_date }}
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Original Price:</span>{{ selectedbook?.price }} Birr
                        </p>
                    </div>
                    <div v-if="selectedbook?.discount" class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Discount Price:</span>{{ selectedbook?.discount }} Birr
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Page Numbers:</span>{{ selectedbook?.page_number }}
                        </p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold pr-4">Edition:</span>{{ selectedbook?.eddition }} Birr
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

