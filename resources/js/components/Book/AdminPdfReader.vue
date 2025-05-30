<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';

import Spinner from "@/components/Layout/Spinner.vue";

GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.min.mjs',
    import.meta.url
).toString();

const props = defineProps({
    selectedBook: {
        type: Object,
        required: true
    }
});

const canvasRef = ref(null);
const containerRef = ref(null);
const currentPage = ref(1);
const totalPages = ref(0);
let pdfDoc = null;
const scale = ref(1);
const openPdf = ref(false);
const isFullScreen = ref(false);
const searchPage = ref("");

const loadPdf = async () => {
    if (!props.selectedBook || !props.selectedBook?.file_url) return;
    try {
        pdfDoc = await getDocument({
            url: props.selectedBook.file_url
        }).promise;
        totalPages.value = pdfDoc.numPages;
        currentPage.value = 1;
        await renderPage(currentPage.value);
        openPdf.value = true;
    } catch (error) {
        console.error("Error loading PDF:", error);
    }
};

const renderPage = async (pageNumber) => {
    if (!pdfDoc) return;
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = canvasRef.value;
    const container = containerRef.value;
    if (!canvas || !container) return;
    const context = canvas.getContext("2d");
    const viewport = page.getViewport({ scale: 1 });
    const containerWidth = container.clientWidth;
    scale.value = containerWidth / viewport.width;
    const scaledViewport = page.getViewport({ scale: scale.value });
    canvas.width = scaledViewport.width;
    canvas.height = scaledViewport.height;
    const renderContext = {
        canvasContext: context,
        viewport: scaledViewport,
    };
    await page.render(renderContext).promise;
};

const nextPage = async () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        await renderPage(currentPage.value);
    }
};

const prevPage = async () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        await renderPage(currentPage.value);
    }
};

const goToPage = async () => {
    const pageNumber = parseInt(searchPage.value);
    if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= totalPages.value) {
        currentPage.value = pageNumber;
        await renderPage(pageNumber);
    } else {
        alert("Invalid page number. Please enter a number between 1 and " + totalPages.value);
    }
};

const toggleFullScreen = () => {
    if (!isFullScreen.value) {
        if (containerRef.value.requestFullscreen) {
            containerRef.value.requestFullscreen();
        } else if (containerRef.value.mozRequestFullScreen) {
            containerRef.value.mozRequestFullScreen();
        } else if (containerRef.value.webkitRequestFullscreen) {
            containerRef.value.webkitRequestFullscreen();
        } else if (containerRef.value.msRequestFullscreen) {
            containerRef.value.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
    isFullScreen.value = !isFullScreen.value;
};

const preventCopy = (event) => {
    event.preventDefault();
};

const preventPrintScreen = (event) => {
    if (event.key === "PrintScreen") {
        event.preventDefault();
        alert("Screenshots are disabled.");
    }
};

const preventCtrlP = (event) => {
    if (event.ctrlKey && event.key.toLowerCase() === "p") {
        event.preventDefault();
        alert("Printing is disabled.");
    }
};

const preventWinShiftS = (event) => {
    if (event.key.toLowerCase() === "s" && event.shiftKey && event.metaKey === false && event.ctrlKey === false) {
        event.preventDefault();
        alert("Screen snipping is disabled.");
    }
};

window.onbeforeprint = () => {
    if (canvasRef.value) {
        canvasRef.value.style.filter = "blur(8px)";
    }
};

window.onafterprint = () => {
    if (canvasRef.value) {
        canvasRef.value.style.filter = "";
    }
};

onMounted(() => {
    if (props.selectedBook && props.selectedBook.file_url) {
        loadPdf();
    }
    // Add prevention event listeners
    document.addEventListener("contextmenu", preventCopy);
    document.addEventListener("keydown", preventPrintScreen);
    document.addEventListener("keydown", preventCtrlP);
    document.addEventListener("keydown", preventWinShiftS);

});

onBeforeUnmount(() => {
    document.removeEventListener("contextmenu", preventCopy);
    document.removeEventListener("keydown", preventPrintScreen);
    document.removeEventListener("keydown", preventCtrlP);
    document.removeEventListener("keydown", preventWinShiftS);
    window.onbeforeprint = null;
    window.onafterprint = null;

});

watch(() => props.selectedBook, (newVal) => {
    if (newVal && newVal.file_url) {
        loadPdf();
    }
}, { immediate: true });
</script>

<template>
    <div v-if="openPdf"
        class="relative w-full flex flex-col items-center min-h-screen">
        <div ref="containerRef"
            :class="{ 'h-screen overflow-y-scroll': isFullScreen }"
            class="w-full  rounded-lg">
            <div class="w-full">
                <canvas ref="canvasRef"
                    class="w-full h-full  select-none"
                    @contextmenu.prevent
                    @dragstart.prevent></canvas>
            </div>
            <!-- Pagination & Controls -->
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center">
                <div class="flex gap-4 mb-4 sm:mb-0 items-center">
                    <button @click="prevPage"
                        :disabled="currentPage === 1"
                        class="px-2 py-2 bg-white rounded text-black disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-left"></i>
                        <span
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition">
                            Previous
                        </span>
                    </button>

                    <span class="font-semibold text-gray-700">
                        Page {{ currentPage }} / {{ totalPages }}
                    </span>

                    <button @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 bg-white rounded text-black disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-right"></i>
                        <span
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition">
                            Next
                        </span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number"
                        v-model="searchPage"
                        placeholder="Page #"
                        class="px-2 py-1 border border-gray-500 rounded w-24" />
                    <button @click="goToPage"
                        class="px-2 py-1 bg-white rounded text-black relative group">
                        <i class="fas fa-search"></i>
                        <span
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition">
                            Go to Page
                        </span>
                    </button>
                </div>
                <button @click="toggleFullScreen"
                    class="px-2 py-2 bg-white rounded text-black flex items-center relative group">
                    <i :class="isFullScreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                    <span
                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition">
                        {{ isFullScreen ? "Exit Full Screen" : "Go Full Screen" }}
                    </span>
                </button>
            </div>

        </div>
    </div>
    <div v-else
        class="p-4">
        <Spinner />
    </div>
</template>

<style scoped>
@media (max-width: 640px) {
    .pdf-container {
        padding: 2rem 1rem;
    }
}

.overflow-auto {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.overflow-auto::-webkit-scrollbar {
    display: none;
}
</style>
