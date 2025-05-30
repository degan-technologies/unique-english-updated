<script setup>
import Axios from 'axios';
import { ref, onUnmounted, watch } from 'vue';
import { storeToRefs } from "pinia";
import { useInstructorStore } from "@/store/useInstructorStore";

const instructorStore = useInstructorStore();
const { selectedCourse, editCourseModule, courseId } = storeToRefs(instructorStore);
const emit = defineEmits(['onAddNewModule']);
const props = defineProps({
    actionType: {
        type: String,
        required: true,
        validator: (value) => ['STORE', 'UPDATE'].includes(value)
    }
});

const title = ref(''); 
const isLoading = ref(false);
const error = ref(null);
 
watch(() => editCourseModule.value, (module) => {
    if (module?.id) {
        title.value = module.title; 
    }
}, { immediate: true });

async function handleSubmit() {
    if (!title.value.trim()) {
        error.value = 'Title is required';
        return;
    }

    error.value = null;
    isLoading.value = true;

    try {
        const data = {
            title: title.value, 
            course_id: courseId.value,
        };

        if (props.actionType === 'STORE') {
            const response = await Axios.post("/api/courses/module", data);
            selectedCourse.value.courseModules = [
                response.data.data,
                ...selectedCourse.value.courseModules
            ]
            resetForm();
        } else {
            const response = await Axios.patch(`/api/courses/module/${editCourseModule.value?.id}`, data);
            selectedCourse.value.courseModules = selectedCourse.value.courseModules.map(
                item => item.id === response.data.data.id ? response.data.data : item
            );

            editCourseModule.value = null;
        }
    } catch (err) {
        error.value = err;
    } finally {
        isLoading.value = false;
    }
}

function resetForm() {
    title.value = ''; 
    courseId.value = null;
    editCourseModule.value = null;
}

function closeModal() {
    if (props.actionType === 'STORE') {
        courseId.value = null;
    } else {
        editCourseModule.value = null;
    }
}

onUnmounted(resetForm);
</script>

<template>
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-10/12 sm:max-w-xl bg-white p-6 rounded-lg shadow-lg overflow-auto max-h-[85vh]" role="dialog"
            aria-modal="true" :aria-labelledby="actionType === 'STORE' ? 'add-module-title' : 'edit-module-title'">
            <h2 :id="actionType === 'STORE' ? 'add-module-title' : 'edit-module-title'"
                class="text-xl font-semibold mb-4">
                {{ actionType === 'STORE' ? 'Add Course Module' : 'Edit Module' }}
            </h2>

            <div v-if="error" class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                {{ error }}
            </div>

            <div class="space-y-4">
                <div>
                    <label for="module-title" class="block text-sm font-medium text-gray-700">Title *</label>
                    <input id="module-title" v-model="title" type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300"
                        placeholder="Module Title" required />
                </div> 
            </div>

            <div class="mt-6 flex justify-end space-x-2">
                <button @click="closeModal"
                    class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors"
                    :disabled="isLoading">
                    Cancel
                </button>
                <button @click="handleSubmit"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center"
                    :disabled="isLoading">
                    <span v-if="isLoading" class="inline-block animate-spin mr-2">↻</span>
                    {{ isLoading ? 'Processing...' : 'Save' }}
                </button>
            </div>
        </div>
    </div>
</template>