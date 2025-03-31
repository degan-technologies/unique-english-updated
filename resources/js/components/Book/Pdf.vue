<script setup>
    import Axios from "axios";
    import { storeToRefs } from "pinia";
    import { useRoute } from "vue-router";
    import { ref, onMounted, watch, onBeforeUnmount } from "vue";
    import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";

    import { UseStudentStore } from "@/store/UseStudentStore";
    import { useAppStore } from "@/store/useAppStore";

    const appStore = useAppStore();
    const studentStore = UseStudentStore();

    const { selectedbookslug } = storeToRefs(studentStore);
    const { authToken } = storeToRefs(appStore);

    const route = useRoute();

    GlobalWorkerOptions.workerSrc = new URL(
        "pdfjs-dist/build/pdf.worker.min.mjs",
        import.meta.url
    ).toString();


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
    selectedbookslug.value = route.query.slug; 

    function getSelectedBook() {
        Axios
            .get(`/api/get-book/${selectedbookslug.value}`)
            .then(res => {
                selectedBook.value = res.data.data;
            })
    }

    // Load PDF
    const loadPdf = async () => {
        if (!selectedBook.value.file_url) return;

        const filename = selectedBook.value.file_url.split('/').pop();
        
        const proxyUrl = `/api/book-pdf/${filename}`;

        try {
            pdfDoc = await getDocument({
                url: proxyUrl,
                httpHeaders: {
                    Authorization: `Bearer ${authToken.value}`,
                },
            }).promise;

            totalPages.value = pdfDoc.numPages;
            renderPage(currentPage.value);
            openPdf.value = true;
        } catch (error) {
            console.error("Error loading PDF via proxy:", error);
        }
    };

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

    // New function: Go to a specific page based on user input
    const goToPage = () => {
        const pageNumber = parseInt(searchPage.value);
        if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= totalPages.value) {
            currentPage.value = pageNumber;
            renderPage(pageNumber);
        } else {
            alert("Invalid page number. Please enter a number between 1 and " + totalPages.value);
        }
    };

    // Security: Block Right-Click & Copy
    const preventCopy = (event) => {
        event.preventDefault();
    };

    // Security: Block DevTools
    const preventDevTools = (event) => {
        if (
            event.key === "F12" ||
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

    // 🔥 Hide Content When Window Loses Focus (Alt+Tab, Snipping Tool, etc.)
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

    watch(()=> route.query.slug ,
    ()=>{
        selectedbookslug.value = route.query.slug;
        getSelectedBook();
    })

    watch(selectedBook, (newVal) => {
        if(newVal && newVal.file_url) {
            loadPdf();
        }
    });

    // Apply Security Measures
    onMounted(() => {
        getSelectedBook();
        loadPdf();
        document.addEventListener("contextmenu", preventCopy);
        document.addEventListener("keydown", preventDevTools);
        document.addEventListener("keydown", blockPrtSc);
        document.addEventListener("dragstart", preventDrag);
        window.addEventListener("blur", hideOnWindowBlur);
        window.addEventListener("focus", showOnWindowFocus);
        // detectSnippingTool();
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
 
</script>

<template>
    <div v-if="openPdf" class="relative flex flex-col mt-24 items-center p-4 bg-gray-100 min-h-screen">

        <div ref="containerRef" :class="{ 'h-screen overflow-y-scroll': isFullScreen }"
            class="w-full max-w-3xl bg-white shadow-md p-4 rounded-lg">
            <div class="overflow-auto">
                <canvas ref="canvasRef" class="w-full h-auto object-contain shadow-lg border rounded-lg select-none"
                    @contextmenu.prevent @dragstart.prevent></canvas>
            </div>
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center">
                <!-- Pagination Buttons -->
                <div class="flex gap-4 mb-4 sm:mb-0 items-center">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="px-4 py-2 bg-slate-50 text-black rounded disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-left text-balck"></i>
                        <span class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-slate-200 text-black text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                            Previous
                        </span>
                    </button>

                    <span class="font-semibold text-gray-700">
                        {{ currentPage }} / {{ totalPages }}
                    </span>
                    <!-- Next Button -->
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="px-4 py-2 bg-slate-50 text-white rounded disabled:opacity-50 relative group">
                        <i class="fas fa-chevron-right text-black"></i>
                        <span class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-slate-200 text-black text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                            Next
                        </span>
                    </button>
                </div>
                <!-- Search Input -->
                <div class="flex items-center gap-2">
                    <input type="number" v-model="searchPage" placeholder="Go to page"
                        class="px-2 py-1 border border-gray-500 rounded w-24" />
                    <button @click="goToPage" class="px-3 py-1 bg-slate-50 text-black rounded relative group">
                        <i class="fas fa-search"></i>
                        <span class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-slate-200 text-black text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                            Search Page
                        </span>
                    </button>
                </div>
                <!-- Full Screen Toggle -->
                <button @click="toggleFullScreen"
                    class="px-4 py-2 bg-slate-50 text-black rounded flex items-center gap-2 relative group">
                    <i :class="isFullScreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                    <span class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-slate-200 text-black text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                        {{ isFullScreen ? "Exit Full Screen" : "Go Full Screen" }}
                    </span>
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
