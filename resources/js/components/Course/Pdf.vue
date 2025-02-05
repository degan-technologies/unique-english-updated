<script setup>
import { ref, onMounted, watch } from "vue";
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

const loadPdf = async () => {
    if (!props.pdfUrl) return;

    pdfDoc = await getDocument(props.pdfUrl).promise;
    totalPages.value = pdfDoc.numPages;
    renderPage(currentPage.value);
};

const renderPage = async (pageNumber) => {
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = canvasRef.value;
    const container = containerRef.value;

    if (!canvas || !container) return;

    const context = canvas.getContext("2d");
    const viewport = page.getViewport({ scale: 1 });

    // Set width proportional to container, but height is dynamic
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

watch(() => props.pdfUrl, loadPdf);
onMounted(loadPdf);
</script>
<template>
    <div class="flex flex-col items-center p-4 bg-gray-100 min-h-screen">
        <div
            ref="containerRef"
            class="w-full max-w-3xl bg-white shadow-md p-4 rounded-lg"
        >
            <!-- Set container to allow scroll if content exceeds height -->
            <div class="overflow-auto">
                <canvas
                    ref="canvasRef"
                    class="w-full h-auto object-contain shadow-lg border rounded-lg"
                ></canvas>
            </div>
            <div class="mt-4 flex justify-center gap-4">
                <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 bg-lime-700 text-white rounded disabled:opacity-50"
                >
                    Previous
                </button>
                <span class="font-semibold text-gray-700">
                    Page {{ currentPage }} / {{ totalPages }}
                </span>
                <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 bg-lime-700 text-white rounded disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>
