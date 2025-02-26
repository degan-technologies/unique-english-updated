G N, [06/02/2025 22:58]
<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";

// Set the worker source manually (For pdfjs-dist v4+)
GlobalWorkerOptions.workerSrc = new URL(
    "pdfjs-dist/build/pdf.worker.min.mjs",
    import.meta.url
).toString();

const props = defineProps({
    pdfUrl: String, // PDF file path
});

const canvasRef = ref(null);
const containerRef = ref(null);
const currentPage = ref(1);
const totalPages = ref(0);
let pdfDoc = null;
let scale = ref(1);
const showOverlay = ref(false);

// Fullscreen toggle state
const isFullScreen = ref(false);

// Load PDF
const loadPdf = async () => {
    if (!props.pdfUrl) return;

    pdfDoc = await getDocument(props.pdfUrl).promise;
    totalPages.value = pdfDoc.numPages;
    renderPage(currentPage.value);
};

// Render PDF Page
const renderPage = async (pageNumber) => {
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = canvasRef.value;
    const container = containerRef.value;

    if (!canvas || !container) return;

    const context = canvas.getContext("2d");
    const viewport = page.getViewport({ scale: 1 });

    // Scale based on container width
    const containerWidth = container.clientWidth;
    scale.value = containerWidth / viewport.width;
    const scaledViewport = page.getViewport({ scale: scale.value });

    // Apply the scaled dimensions
    canvas.width = scaledViewport.width;
    canvas.height = scaledViewport.height;

    const renderContext = {
        canvasContext: context,
        viewport: scaledViewport,
    };

    await page.render(renderContext).promise;
};

// Fullscreen functions
const toggleFullScreen = () => {
    if (!isFullScreen.value) {
        if (containerRef.value.requestFullscreen) {
            containerRef.value.requestFullscreen();
        } else if (containerRef.value.mozRequestFullScreen) {
            // Firefox
            containerRef.value.mozRequestFullScreen();
        } else if (containerRef.value.webkitRequestFullscreen) {
            // Chrome, Safari, Opera
            containerRef.value.webkitRequestFullscreen();
        } else if (containerRef.value.msRequestFullscreen) {
            // IE/Edge
            containerRef.value.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            // Firefox
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            // Chrome, Safari, Opera
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            // IE/Edge
            document.msExitFullscreen();
        }
    }
    isFullScreen.value = !isFullScreen.value;
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

// Security: Block Right-Click & Copy
const preventCopy = (event) => {
    event.preventDefault();
};

// Security: Block DevTools
const preventDevTools = (event) => {
    if (
        event.key === "F12" 
        (event.ctrlKey && event.shiftKey && ["I", "J", "C"].includes(event.key))
    ) {
        event.preventDefault();
        alert("Developer tools are disabled.");
    }
};

// Security: Block Dragging of Canvas
const preventDrag = (event) => {
    event.preventDefault();
};

// Security: Prevent Screenshots (Block PrtSc Key)
const blockPrtSc = (event) => {
    if (event.key === "PrintScreen") {
        event.preventDefault();
        alert("Screenshots are disabled.");
        showOverlay.value = true; // Show overlay
        setTimeout(() => {
            showOverlay.value = false; // Remove overlay after 5 sec
        }, 5000);
    }
};

// 🔥 Stronger Detection for Snipping Tool & Screenshot Attempts
const detectSnippingTool = () => {
    setInterval(() => {
        let screenWidth = window.outerWidth - window.innerWidth > 200;
        let screenHeight = window.outerHeight - window.innerHeight > 200;

        if (screenWidth || screenHeight) {
            showOverlay.value = true; // Show overlay
            alert("Screen capture detected! Content hidden.");
        } else {
            showOverlay.value = false; // Remove overlay if no capture
        }
    }, 500); // Check every 500ms (faster detection)
};

// 🔥  mt-24Hide Content When Window Loses Focus (Alt+Tab, Snipping Tool, etc.)
const hideOnWindowBlur = () => {
    showOverlay.value = true;
};

const showOnWindowFocus = () => {
    showOverlay.value = false;
};

// 🔥 Detect Window Resize (Common Snipping Tool Behavior)
const detectResize = () => {
    window.addEventListener("resize", () => {
        showOverlay.value = true;
        setTimeout(() => {
            showOverlay.value = false;
        }, 3000);
    });
};

// Apply Security Measures
onMounted(() => {
    loadPdf();
    document.addEventListener("contextmenu", preventCopy);
    document.addEventListener("keydown", preventDevTools);
    document.addEventListener("keydown", blockPrtSc);
    document.addEventListener("dragstart", preventDrag);
    window.addEventListener("blur", hideOnWindowBlur);
    window.addEventListener("focus", showOnWindowFocus);
    detectSnippingTool();
    detectResize();
});

onBeforeUnmount(() => {
    document.removeEventListener("contextmenu", preventCopy);
    document.removeEventListener("keydown", preventDevTools);
    document.removeEventListener("keydown", blockPrtSc);
    document.removeEventListener("dragstart", preventDrag);
    window.removeEventListener("blur", hideOnWindowBlur);
    window.removeEventListener("focus", showOnWindowFocus);
});

// Watch for PDF URL changes
watch(() => props.pdfUrl, loadPdf);
</script>

<template>
    <div
        class="relative flex flex-col mt-24 items-center p-4 bg-gray-100 min-h-screen"
    >
        <!-- 🔥 Dynamic Overlay (Blocks screenshots/snipping tool in real-time) -->
        <div
            v-if="showOverlay"
            class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center text-white text-2xl font-bold z-50"
        >
            Screenshot Blocked!
        </div>

        <div
            ref="containerRef"
            :class="{ 'h-screen overflow-y-scroll': isFullScreen }"
            class="w-full max-w-3xl bg-white shadow-md p-4 rounded-lg"
        >
            <!-- Set container to allow scroll if content exceeds height -->
            <div class="overflow-auto">
                <canvas
                    ref="canvasRef"
                    class="w-full h-auto object-contain shadow-lg border rounded-lg select-none"
                    @contextmenu.prevent
                    @dragstart.prevent
                ></canvas>
            </div>
            <div
                class="mt-4 flex flex-col sm:flex-row justify-between items-center"
            >
                <!-- Pagination Buttons -->
                <div class="flex gap-4 mb-4 sm:mb-0">
                    <button
                        @click="prevPage"
                        :disabled="currentPage === 1"
                        class="px-4 py-2 bg-lime-700 text-white rounded disabled:opacity-50"
                    >
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="font-semibold text-gray-700">
                        Page {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 bg-lime-700 text-white rounded disabled:opacity-50"
                    >
                        <i class="fas fa-chevron-right"></i> Next
                    </button>
                </div>
                <button
                    @click="toggleFullScreen"
                    class="px-4 py-2 bg-lime-500 text-white rounded flex items-center gap-2"
                >
                    <i
                        :class="
                            isFullScreen ? 'fas fa-compress' : 'fas fa-expand'
                        "
                    ></i>
                    {{ isFullScreen ? "Exit Full Screen" : "Go Full Screen" }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Make PDF container responsive */
@media (max-width: 640px) {
    .pdf-container {
        padding: 2rem 1rem;
    }
}

@media (max-width: 640px) {
    .pagination-buttons {
        flex-direction: column;
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