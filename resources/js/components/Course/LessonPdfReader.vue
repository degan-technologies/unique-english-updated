<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";

import Spinner from "@/components/Layout/Spinner.vue";

GlobalWorkerOptions.workerSrc = new URL(
    "pdfjs-dist/build/pdf.worker.min.mjs",
    import.meta.url
).toString();

const props = defineProps({
    selectedLesson: {
        type: Object,
        required: true,
    },
});

const canvasRef = ref(null);
const containerRef = ref(null);
const currentPage = ref(1);
const totalPages = ref(0);
let pdfDoc = null;
const openPdf = ref(false);
const isFullScreen = ref(false);
const searchPage = ref("");

async function loadPdf() {
    if (!props.selectedLesson?.content_url) return;
    try {
        pdfDoc = await getDocument({ url: props.selectedLesson.content_url })
            .promise;
        totalPages.value = pdfDoc.numPages;
        currentPage.value = 1;
        await renderPage(currentPage.value);
        openPdf.value = true;
    } catch (err) {
        console.error(err);
    }
}

async function renderPage(pageNumber) {
    if (!pdfDoc) return;
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = canvasRef.value;
    const container = containerRef.value;
    if (!canvas || !container) return;
    const context = canvas.getContext("2d");

    // Compute CSS-scaled viewport
    const unscaledViewport = page.getViewport({ scale: 1 });
    const containerWidth = container.clientWidth;
    const scaleToFit = containerWidth / unscaledViewport.width;
    const viewport = page.getViewport({ scale: scaleToFit });

    // Account for device pixel ratio
    const outputScale = window.devicePixelRatio || 1;
    canvas.width = Math.floor(viewport.width * outputScale);
    canvas.height = Math.floor(viewport.height * outputScale);
    canvas.style.width = `${Math.floor(viewport.width)}px`;
    canvas.style.height = `${Math.floor(viewport.height)}px`;

    // Scale the drawing context so that 1 unit in the PDF
    // equals 1 CSS pixel on the screen
    context.setTransform(outputScale, 0, 0, outputScale, 0, 0);

    // Render
    await page.render({ canvasContext: context, viewport }).promise;
}

async function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        await renderPage(currentPage.value);
    }
}

async function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
        await renderPage(currentPage.value);
    }
}

async function goToPage() {
    const n = parseInt(searchPage.value, 10);
    if (n >= 1 && n <= totalPages.value) {
        currentPage.value = n;
        await renderPage(n);
    }
}

function toggleFullScreen() {
    const el = containerRef.value;
    if (!isFullScreen.value) {
        el.requestFullscreen?.() ||
            el.mozRequestFullScreen?.() ||
            el.webkitRequestFullscreen?.() ||
            el.msRequestFullscreen?.();
    } else {
        document.exitFullscreen?.() ||
            document.mozCancelFullScreen?.() ||
            document.webkitExitFullscreen?.() ||
            document.msExitFullscreen?.();
    }
    isFullScreen.value = !isFullScreen.value;
}

// Prevent copying/printing
const preventCopy = (e) => e.preventDefault();
const preventKey = (e) => {
    if (
        e.key === "PrintScreen" ||
        (e.ctrlKey && e.key.toLowerCase() === "p") ||
        (e.shiftKey && e.key.toLowerCase() === "s")
    ) {
        e.preventDefault();
    }
};
window.onbeforeprint = () => (canvasRef.value.style.filter = "blur(8px)");
window.onafterprint = () => (canvasRef.value.style.filter = "");

onMounted(() => {
    if (props.selectedLesson?.content_type === 2) loadPdf();
    document.addEventListener("contextmenu", preventCopy);
    document.addEventListener("keydown", preventKey);
});

onBeforeUnmount(() => {
    document.removeEventListener("contextmenu", preventCopy);
    document.removeEventListener("keydown", preventKey);
    window.onbeforeprint = null;
    window.onafterprint = null;
});

watch(
    () => props.selectedLesson,
    (newLesson) => {
        if (newLesson?.content_type === 2) loadPdf();
    },
    { immediate: true }
);
</script>

<template>
    <div v-if="openPdf" class="relative flex flex-col items-center p-4 h-fit">
        <div ref="containerRef" :class="{ 'h-screen overflow-y-scroll': isFullScreen }"
            class="w-full bg-white rounded-lg pdf-container">
            <div class="overflow-auto">
                <canvas ref="canvasRef" class="w-full h-auto object-contain shadow-lg border rounded-lg select-none"
                    @contextmenu.prevent @dragstart.prevent></canvas>
            </div>
            <!-- Pagination & Controls -->
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center">
                <div class="flex gap-4 mb-4 sm:mb-0 items-center">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="px-2 py-2 bg-white rounded text-black disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="font-semibold text-gray-700">
                        Page {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="px-4 py-2 bg-white rounded text-black disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" v-model="searchPage" placeholder="Page #"
                        class="px-2 py-1 border border-gray-500 rounded w-24" />
                    <button @click="goToPage" class="px-2 py-1 bg-white rounded text-black relative group">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <button @click="toggleFullScreen"
                    class="px-2 py-2 bg-white rounded text-black flex items-center relative group">
                    <i :class="isFullScreen ? 'fas fa-compress' : 'fas fa-expand'
                        "></i>
                </button>
            </div>
        </div>
    </div>
    <div v-else class="p-4">
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
