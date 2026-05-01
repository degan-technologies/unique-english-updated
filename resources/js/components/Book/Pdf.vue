<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";

import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";

import Spinner from "@/components/Layout/Spinner.vue";

const appStore = useAppStore();
const studentStore = UseStudentStore();

const { selectedbookslug } = storeToRefs(studentStore);
const { authToken } = storeToRefs(appStore);

const route = useRoute();

GlobalWorkerOptions.workerSrc = "/js/pdf.worker.min.mjs";

const canvasRef = ref(null);
const containerRef = ref(null);
const currentPage = ref(1);
const totalPages = ref(0);
let pdfDoc = null;
let scale = ref(1);
const showOverlay = ref(false);
const isFullScreen = ref(false);
const searchPage = ref("");
const selectedBook = ref("");
const openPdf = ref(false);
const isMobile = ref(window.innerWidth < 768);
const renderQuality = ref(isMobile.value ? 1.5 : 2);
const dpiScale = window.devicePixelRatio || 1;

selectedbookslug.value = route.query.slug;

// Handle window resize
const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
    if (pdfDoc && currentPage.value) {
        renderPage(currentPage.value);
    }
};

function getSelectedBook() {
    Axios.get(`/api/get-book/${selectedbookslug.value}`).then((res) => {
        selectedBook.value = res.data.data;
    }).then(()=>{
        loadPdf();
    });
    
}
 
async function loadPdf() {
    if (!selectedBook.value.file_url) return;

    try {
        const response = await fetch(`/api${selectedBook.value.file_url}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${authToken.value}`,
            },
            body: JSON.stringify({
                filename: selectedBook.value.file_url,  
            }),
        });

        if (!response.ok) throw new Error("Failed to load PDF");

        const pdfData = await response.arrayBuffer();

        pdfDoc = await getDocument({ data: pdfData }).promise;
        totalPages.value = pdfDoc.numPages;
        currentPage.value = 1;
        await renderPage(currentPage.value);
        openPdf.value = true;
    } catch (err) {
        console.error("PDF Load Error:", err);
    }
}

const renderPage = async (pageNumber) => {
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = canvasRef.value;
    const container = containerRef.value;

    if (!canvas || !container) return;

    const context = canvas.getContext("2d");
    const viewport = page.getViewport({ scale: 1 });

    // Adjust scale based on device and quality settings
    const containerWidth = container.clientWidth;
    scale.value = (containerWidth / viewport.width) * renderQuality.value;

    // Apply DPI scaling for high-resolution displays
    const scaledViewport = page.getViewport({
        scale: scale.value * dpiScale,
    });

    // Set canvas display size
    canvas.style.width = `${containerWidth}px`;
    canvas.style.height = `${(containerWidth * scaledViewport.height) / scaledViewport.width
        }px`;

    // Set canvas render size (accounting for device pixel ratio)
    canvas.width = scaledViewport.width;
    canvas.height = scaledViewport.height;

    // Apply CSS transforms for crisp rendering
    context.scale(dpiScale, dpiScale);

    const renderContext = {
        canvasContext: context,
        viewport: page.getViewport({ scale: scale.value }),
    };

    await page.render(renderContext).promise;
}; 

// Pagination handlers
const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        renderPage(currentPage.value);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        renderPage(currentPage.value);
    }
};

const goToPage = () => {
    const pageNumber = parseInt(searchPage.value);
    if (
        !isNaN(pageNumber) &&
        pageNumber >= 1 &&
        pageNumber <= totalPages.value
    ) {
        currentPage.value = pageNumber;
        renderPage(pageNumber);
    } else { 
    }
};

// Security functions
const preventCopy = (event) => {
    event.preventDefault();
};

const preventDevTools = (event) => {
    if (
        event.key === "F12" ||
        (event.ctrlKey && event.shiftKey && ["I", "J", "C"].includes(event.key))
    ) {
        event.preventDefault();
        alert("Developer tools are disabled.");
    }
};

const preventDrag = (event) => {
    event.preventDefault();
};

const blockPrtSc = (event) => {
    if (event.key === "PrintScreen") {
        event.preventDefault();
        alert("Screenshots are disabled.");
        showOverlay.value = true;
        setTimeout(() => {
            showOverlay.value = false;
        }, 5000);
    }
}; 

const hideOnWindowBlur = () => {
    showOverlay.value = true;
};

const showOnWindowFocus = () => {
    showOverlay.value = false;
};

const detectResize = () => {
    window.addEventListener("resize", () => {
        showOverlay.value = true;
        setTimeout(() => {
            showOverlay.value = false;
        }, 3000);
    });
};

watch(
    () => route.query.slug,
    () => {
        selectedbookslug.value = route.query.slug;
        getSelectedBook();
    }
);

watch(selectedBook, (newVal) => {
    if (newVal && newVal.file_url) {
        loadPdf();
    }
});

onMounted(() => {
    getSelectedBook();    
    window.addEventListener("resize", handleResize);
    document.addEventListener("contextmenu", preventCopy);
    document.addEventListener("keydown", preventDevTools);
    document.addEventListener("keydown", blockPrtSc);
    document.addEventListener("dragstart", preventDrag);
    window.addEventListener("blur", hideOnWindowBlur);
    window.addEventListener("focus", showOnWindowFocus);
    detectResize();
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    document.removeEventListener("contextmenu", preventCopy);
    document.removeEventListener("keydown", preventDevTools);
    document.removeEventListener("keydown", blockPrtSc);
    document.removeEventListener("dragstart", preventDrag);
    window.removeEventListener("blur", hideOnWindowBlur);
    window.removeEventListener("focus", showOnWindowFocus);
});
</script>

<template>
    <div v-if="!openPdf">
        <Spinner/>
    </div>
    <div v-else class="relative flex flex-col mt-24 items-center p-4 min-h-screen">
        <div ref="containerRef" :class="{ 'h-screen overflow-y-scroll': isFullScreen }"
            class="w-full max-w-3xl bg-white shadow-md p-4 rounded-lg">
            <div class="overflow-auto">
                <canvas ref="canvasRef"
                    class="w-full max-w-full h-auto object-contain shadow-lg border rounded-lg select-none"
                    @contextmenu.prevent @dragstart.prevent></canvas>
            </div> 
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center">
                <!-- Pagination Buttons -->
                <div class="flex gap-4 mb-4 sm:mb-0 items-center">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="px-4 py-2 bg-slate-50 text-black rounded disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-left text-balck"></i>
                    </button>

                    <span class="font-semibold text-gray-700">
                        {{ currentPage }} / {{ totalPages }}
                    </span>

                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="px-4 py-2 bg-slate-50 text-white rounded disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-right text-black"></i>
                    </button>
                </div>

                <!-- Search Input -->
                <div class="flex items-center gap-2">
                    <input type="number" v-model="searchPage" placeholder="Go to page"
                        class="px-2 py-1 border border-gray-500 rounded w-24" @keyup.enter="goToPage" />
                    <button @click="goToPage" class="px-3 py-1 bg-slate-50 text-black rounded relative group">
                        <i class="fas fa-search"></i>
                    </button> 
                </div>
            </div>
        </div>
    </div>
</template> 