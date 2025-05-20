<script setup>
import { ref, onMounted, watch } from "vue";

const props = defineProps({
    selectedLesson: {
        type: Object,
        required: true,
    },
    selectedCourse: {
        type: Object,
        required: true,
    },
});

const content = ref("");

const loadNoteForLesson = () => {
    if (props.selectedLesson?.id) {
        const savedNote = localStorage.getItem(`lesson-note-${props.selectedLesson.id}`);
        content.value = savedNote !== null ? savedNote : props.selectedLesson.content || "";
    }
};

const saveContent = () => {
    if (props.selectedLesson?.id) {
        localStorage.setItem(`lesson-note-${props.selectedLesson.id}`, content.value);
    }
};

onMounted(() => {
    if (props.selectedLesson) {
        content.value = props.selectedLesson.content || "";
        loadNoteForLesson();
    }
});

watch(
    () => props.selectedLesson,
    (newLesson) => {
        if (newLesson && newLesson.id) {
            loadNoteForLesson();
        }
    }
);
</script>

<template>
    <div class="w-full bg-gray-50 flex justify-center items-start">
        <div class="bg-white shadow-lg rounded-lg p-1 w-full max-w-5xl">
            <textarea v-model="content"
                class="w-full h-64 border border-gray-300 rounded-lg p-4 bg-gray-50 resize-none focus:outline-none focus:ring-2 focus:ring-lime-600"
                placeholder="Start writing your content here..."></textarea>
            <div class="flex justify-end mt-4">
                <button @click="saveContent"
                    class="bg-lime-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-lime-700 transition">
                    Save
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
textarea {
    font-family: "Inter", sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    min-height: 200px;
    overflow-y: auto;
}

@media (max-width: 640px) {
    textarea {
        min-height: 150px;
    }
}
</style>