<template>
    <div class="reader-container">
        <div class="pdf-viewer-container">
            <!-- PDF Viewer -->
            <iframe
                :key="currentPage"
                ref="pdfIframe"
                :src="iframeSrc"
                class="pdf-iframe"
            ></iframe>

            <!-- Controls -->
            <div class="controls-container">
                <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    class="nav-button prev"
                >
                    Prev
                </button>

                <span class="page-info">
                    Page {{ currentPage }} / {{ numPages }}
                </span>

                <button
                    @click="nextPage"
                    :disabled="currentPage >= numPages"
                    class="nav-button next"
                >
                    Next
                </button>
            </div>

            <!-- Zoom Controls -->
            <div class="zoom-controls">
                <button @click="zoomOut" class="zoom-button">-</button>
                <span class="zoom-info">{{ zoomLevel }}%</span>
                <button @click="zoomIn" class="zoom-button">+</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const pdfUrl = "/images/req.pdf"; // Path to your PDF file
const currentPage = ref(1);
const numPages = ref(29); // Example: Set the number of pages to 29 (replace with actual page count)
const zoomLevel = ref(100);
const iframeSrc = ref("");

// Adjust zoom level of the iframe
const zoomIn = () => {
    zoomLevel.value += 10;
    updateZoom();
};

const zoomOut = () => {
    if (zoomLevel.value > 50) {
        zoomLevel.value -= 10;
        updateZoom();
    }
};

// Update the iframe zoom level
const updateZoom = () => {
    const iframe = document.querySelector("iframe");
    const zoom = zoomLevel.value / 100;
    iframe.style.transform = `scale(${zoom})`;
    iframe.style.transformOrigin = "top left";
};

// Navigate to the next page
const nextPage = () => {
    if (currentPage.value < numPages.value) {
        currentPage.value++;
        updateIframeSrc();
    }
};

// Navigate to the previous page
const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        updateIframeSrc();
    }
};

// Update iframe source with page number
const updateIframeSrc = () => {
    const page = currentPage.value;
    iframeSrc.value = `${pdfUrl}#page=${page}`; // Update the iframe src with page number
};

// Initialize the iframe with the first page
updateIframeSrc();
</script>

<style scoped>
/* Overall layout for the reader container */
.reader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f4f7fa;
    padding: 20px;
}

/* Container for the PDF viewer and controls */
.pdf-viewer-container {
    background-color: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 800px;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
}

/* PDF iframe styling */
.pdf-iframe {
    width: 100%;
    height: 600px;
    border: none;
}

/* Controls section (prev, next, and page info) */
.controls-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-top: 15px;
}

.nav-button {
    background-color: #4ade80; /* lime-700 */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.nav-button:disabled {
    background-color: #d6d6d6;
    cursor: not-allowed;
}

.nav-button:hover:not(:disabled) {
    background-color: #16a34a; /* A slightly darker lime shade on hover */
}

.page-info {
    font-size: 16px;
    font-weight: 600;
}

/* Zoom Controls */
.zoom-controls {
    display: flex;
    justify-content: center;
    margin-top: 15px;
}

.zoom-button {
    background-color: #4ade80; /* lime-700 */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.3s;
}

.zoom-button:hover {
    background-color: #16a34a; /* A slightly darker lime shade on hover */
}

.zoom-info {
    margin: 0 10px;
    font-size: 18px;
    font-weight: 500;
}
</style>
