<template>
    <div class="w-full bg-gray-50 flex justify-center items-start">
        <div class="bg-white shadow-lg rounded-lg p-1 w-full max-w-5xl">
            <!-- Editor Container -->
            <div
                id="editor-container"
                class="border border-gray-300 rounded-lg p-4 bg-gray-50 overflow-hidden"
            ></div>

            <!-- Action Buttons -->
            <div class="flex justify-end mt-4">
                <button
                    @click="saveContent"
                    class="bg-lime-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-lime-700 transition"
                >
                    Save
                </button>
            </div>

            <!-- Preview Section -->
            <div v-if="content" class="mt-6">
                <h2 class="text-xl font-bold text-gray-700 mb-4">
                    Content Preview
                </h2>
                <div
                    v-html="content"
                    class="prose max-w-none bg-gray-50 border border-gray-300 rounded-lg shadow p-4 overflow-x-auto"
                ></div>
            </div>
        </div>
    </div>
</template>

<script>
import Quill from "quill";
import "quill/dist/quill.snow.css"; // Default Quill theme
import katex from "katex"; // For equations
import "katex/dist/katex.min.css";

export default {
    name: "App",
    data() {
        return {
            quillEditor: null,
            content: "",
        };
    },
    methods: {
        initializeEditor() {
            const toolbarOptions = [
                [{ header: [1, 2, 3, 4, false] }], // H1, H2, H3, H4
                ["bold", "italic", "underline", "strike"], // Text styles
                [{ script: "sub" }, { script: "super" }], // Subscript & superscript
                [{ color: [] }, { background: [] }], // Text color & background
                [{ font: [] }], // Font sizes
                [{ list: "ordered" }, { list: "bullet" }], // Ordered & unordered lists
                [{ align: [] }], // Alignments
                ["link", "image", "blockquote", "code-block"], // Insert options
                ["math"], // Mathematical equations
                ["clean"], // Clear formatting
            ];

            // Initialize Quill
            this.quillEditor = new Quill("#editor-container", {
                theme: "snow",
                modules: {
                    toolbar: toolbarOptions,
                },
                placeholder: "Start writing your content here...",
            });

            // Adjust height as user types
            const editorContainer = document.querySelector("#editor-container");
            this.quillEditor.on("text-change", () => {
                this.content = this.quillEditor.root.innerHTML;
                this.adjustHeight(editorContainer);
            });
        },

        adjustHeight(container) {
            container.style.height = "auto"; // Reset height to calculate new height
            container.style.height = container.scrollHeight + "px";
        },

        saveContent() {
            // Simulate saving content
            alert("Content saved successfully!");
        },
    },
    mounted() {
        this.initializeEditor();
    },
};
</script>

<style scoped>
/* Tailwind ensures responsive and clean design */
#editor-container {
    font-family: "Inter", sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    min-height: 200px; /* Minimum height for the editor */
    overflow-y: hidden; /* Hide overflow to make height adjustment seamless */
    transition: height 0.2s ease-in-out; /* Smooth height changes */
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
}

th {
    background-color: #f9fafb;
    font-weight: bold;
    text-align: center;
}

/* Responsive padding adjustments */
@media (max-width: 640px) {
    .min-h-screen {
        padding-top: 0; /* No extra padding at the top */
    }

    #editor-container {
        min-height: 150px; /* Adjust the minimum height for smaller screens */
    }
}
</style>
