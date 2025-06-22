<script setup>
import { ref, onMounted, watch, defineProps } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
    selectedCourse: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits();

const courseOverview = ref(props.selectedCourse.overview || "");

let quillEditor = null;

// Initialize Quill editor
const initializeEditor = () => {
    quillEditor = new Quill("#overview-editor-container", {
        theme: "snow",
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ script: "sub" }, { script: "super" }],
                [{ color: [] }, { background: [] }],
                [{ font: [] }],
                [{ list: "ordered" }, { list: "bullet" }],
                [{ align: [] }],
                ["link", "image", "blockquote", "code-block"],
                ["clean"],
            ],
        },
        placeholder: "Write your course overview...",
    });

    quillEditor.root.innerHTML = courseOverview.value;

    quillEditor.on("text-change", () => {
        courseOverview.value = quillEditor.root.innerHTML;
        emit("update-overview", courseOverview.value);
    });
};

watch(
    () => props.selectedCourse,
    (newCourse) => {
        courseOverview.value = newCourse.overview || "";
        if (quillEditor) {
            quillEditor.root.innerHTML = courseOverview.value;
        }
    },
    { immediate: true }
);

onMounted(() => {
    initializeEditor();
});
</script>

<template>
    <div class="w-full bg-white flex justify-center items-start">
        <div class="bg-white rounded-lg p-1 w-full ">
            <div
                id="overview-editor-container"
                class="border border-gray-300 rounded-lg p-4 bg-white"
            ></div>
        </div>
    </div>
</template>

<style scoped>
#overview-editor-container {
    font-family: "Inter", sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    min-height: 200px;
    overflow-y: hidden;
    transition: height 0.2s ease-in-out;
}
</style>
