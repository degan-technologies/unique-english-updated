<script setup>
import Axios from "axios";
import { useToast } from "vue-toastification";
import { onMounted, ref, computed } from "vue";
import AddQuestion from "@/components/Quize/AddQuestion.vue";

const toast = useToast();

// State
const qMetaDatas = ref([]);
const selectedExam = ref(null);
const showExamForm = ref(true);
const hasChanges = ref(false);
const selectedQuestion = ref(null);
const isNewExamMode = ref(false);
const currentQuestionPage = ref(1);
const questionsPerPage = 5;
const hoveredQuestion = ref(null);

const metaData = ref({
    title: "",
    instruction: "",
});

const props = defineProps({
    courseID: Number,
    moduleID: Number
});

// Computed
const visibleQuestions = computed(() => {
    if (selectedExam.value?.questions) {
        const start = (currentQuestionPage.value - 1) * questionsPerPage;
        return selectedExam.value.questions.slice(start, start + questionsPerPage);
    }
    return [];
});

const totalQuestionPages = computed(() => {
    return selectedExam.value?.questions
        ? Math.ceil(selectedExam.value.questions.length / questionsPerPage)
        : 0;
});

const confirmDialog = ref({
    visible: false,
    message: "",
    onConfirm: null,
});

// Methods
function showConfirm(message, onConfirm) {
    confirmDialog.value = { visible: true, message, onConfirm };
}

function hideConfirm() {
    confirmDialog.value.visible = false;
}

function selectExam(exam) {
    isNewExamMode.value = false;
    if (selectedExam.value?.id === exam.id) {
        selectedExam.value = null;
    } else {
        selectedExam.value = {
            ...exam,
            instruction: exam.instruction || ""
        };
        metaData.value = {
            title: exam.title || "",
            instruction: exam.instruction || "",
        };
        showExamForm.value = true;
        currentQuestionPage.value = 1;
        selectedQuestion.value = null;
        hasChanges.value = false;
    }
}

function toggleExamForm() {
    showExamForm.value = !showExamForm.value;
}

function checkForChanges() {
    if (!selectedExam.value) return;
    hasChanges.value =
        metaData.value.title !== selectedExam.value.title ||
        metaData.value.instruction !== selectedExam.value.instruction;
}

function prepareEditQuestion(question) {
    selectedQuestion.value = question;
    showExamForm.value = false;
}

function createNewExam() {
    isNewExamMode.value = true;
    selectedExam.value = null;
    showExamForm.value = true;
    selectedQuestion.value = null;
    metaData.value = { title: "", instruction: "" };
}

function showAddQuestionForm() {
    selectedQuestion.value = null;
    showExamForm.value = false;
}

async function fetchExams() {
    try {
        const res = await Axios.get("/api/get-module-quizes", {
            params: {
                course_id: props.courseID,
                module_id: props.moduleID
            }
        });
        const allExams = res.data.data;
        qMetaDatas.value = allExams.filter(
            exam => exam.course_id === props.courseID &&
                exam.course_module_id === props.moduleID
        );
    } catch {
        toast.error("Failed to fetch exams");
    }
}

async function saveExam() {
    if (!metaData.value.title.trim()) {
        toast.error("Exam title is required");
        return;
    }

    const payload = {
        title: metaData.value.title,
        instruction: metaData.value.instruction,
        module_id: props.moduleID,
        course_id: props.courseID
    };

    try {
        const res = isNewExamMode.value
            ? await Axios.post("/api/QMetaData", payload)
            : await Axios.put(`/api/QMetaData/${selectedExam.value.id}`, payload);

        selectedExam.value = res.data.data;

        if (isNewExamMode.value) {
            qMetaDatas.value.push(res.data.data);
            isNewExamMode.value = false;
        } else {
            qMetaDatas.value = qMetaDatas.value.map(q =>
                q.id === res.data.data.id ? res.data.data : q
            );
        }

        hasChanges.value = false;
        toast.success(`Exam ${isNewExamMode.value ? 'created' : 'updated'} successfully`);
    } catch {
        toast.error(`Failed to ${isNewExamMode.value ? 'create' : 'update'} exam`);
    }
}

async function deleteExam(examId) {
    showConfirm("Are you sure you want to delete this exam?", async () => {
        try {
            await Axios.delete(`/api/QMetaData/${examId}`);
            qMetaDatas.value = qMetaDatas.value.filter(q => q.id !== examId);
            if (selectedExam.value?.id === examId) {
                selectedExam.value = null;
            }
            toast.success("Exam deleted successfully");
        } catch {
            toast.error("Error deleting exam");
        } finally {
            hideConfirm();
        }
    });
}

async function deleteQuestion(questionId) {
    showConfirm("Are you sure you want to delete this question?", async () => {
        try {
            await Axios.delete(`/api/quize/${questionId}`);
            selectedExam.value.questions = selectedExam.value.questions.filter(
                q => q.id !== questionId
            );
            if (currentQuestionPage.value > totalQuestionPages.value) {
                currentQuestionPage.value = 1;
            }
            toast.success("Question deleted successfully");
        } catch {
            toast.error("Error deleting question");
        } finally {
            hideConfirm();
        }
    });
}

function changeQuestionPage(page) {
    if (page >= 1 && page <= totalQuestionPages.value) {
        currentQuestionPage.value = page;
    }
}

onMounted(() => {
    fetchExams();
    createNewExam();
});
</script>

<template>
    <div class="w-full mx-auto p-4">
        <!-- Confirmation Dialog -->
        <div v-if="confirmDialog.visible" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                </div>
                <p class="text-gray-600 mb-6">{{ confirmDialog.message }}</p>
                <div class="flex justify-end gap-3">
                    <button @click="hideConfirm" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                        Cancel
                    </button>
                    <button @click="confirmDialog.onConfirm"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Left Panel - Exams List -->
            <div class="md:w-1/3 bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Exams</h2>
                    <button @click="createNewExam" class="p-2 text-green-600 hover:bg-green-50 rounded-full">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div class="space-y-2">
                    <div v-for="exam in qMetaDatas" :key="exam.id" class="group">
                        <div @click="selectExam(exam)"
                            class="flex items-center justify-between p-3 rounded-lg cursor-pointer transition-colors"
                            :class="{
                                'bg-green-50': selectedExam?.id === exam.id,
                                'hover:bg-gray-50': selectedExam?.id !== exam.id
                            }">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                    <i class="fas fa-file-alt text-sm"></i>
                                </div>
                                <h3 class="font-medium text-gray-800 line-clamp-1">{{ exam.title }}</h3>
                            </div>
                            <button @click.stop="deleteExam(exam.id)"
                                class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>

                        <!-- Questions for selected exam -->
                        <div v-if="selectedExam?.id === exam.id"
                            class="ml-4 pl-4 border-l-2 border-green-200 mt-2 space-y-2">
                            <div v-for="(question, index) in visibleQuestions" :key="question.id"
                                @mouseover="hoveredQuestion = question.id" @mouseleave="hoveredQuestion = null"
                                @click="prepareEditQuestion(question)"
                                class="flex items-center justify-between p-2 rounded hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500 w-5">
                                        {{ (currentQuestionPage - 1) * questionsPerPage + index + 1 }}.
                                    </span>
                                    <p class="text-sm text-gray-700 line-clamp-1">{{ question.question }}</p>
                                </div>
                                <button @click.stop="deleteQuestion(question.id)"
                                    class="text-red-400 hover:text-red-600 p-1"
                                    :class="{ 'opacity-100': hoveredQuestion === question.id, 'opacity-0': hoveredQuestion !== question.id }">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalQuestionPages > 1" class="flex justify-center gap-2 mt-3">
                                <button @click="changeQuestionPage(currentQuestionPage - 1)"
                                    :disabled="currentQuestionPage === 1"
                                    class="w-8 h-8 flex items-center justify-center rounded-full disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <span class="flex items-center text-sm text-gray-500">
                                    Page {{ currentQuestionPage }} of {{ totalQuestionPages }}
                                </span>
                                <button @click="changeQuestionPage(currentQuestionPage + 1)"
                                    :disabled="currentQuestionPage === totalQuestionPages"
                                    class="w-8 h-8 flex items-center justify-center rounded-full disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Exam/Question Form -->
            <div class="md:w-2/3 bg-white rounded-lg border border-gray-200 p-6">
                <div v-if="showExamForm">
                    <!-- Exam Form Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold">
                            {{ isNewExamMode ? 'Create New Exam' : 'Edit Exam' }}
                        </h2>
                        <button v-if="!isNewExamMode" @click="toggleExamForm"
                            class="p-2 text-gray-500 hover:bg-gray-50 rounded-full">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Exam Form -->
                    <form @submit.prevent="saveExam" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Exam Title *</label>
                            <input v-model="metaData.title" @input="checkForChanges" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter exam title" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                            <textarea v-model="metaData.instruction" @input="checkForChanges" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter exam instructions (optional)"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button v-if="!isNewExamMode" @click="showAddQuestionForm" type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Add Question
                            </button>
                            <button type="submit" :disabled="!hasChanges && !isNewExamMode"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ isNewExamMode ? 'Create Exam' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Question Form -->
                <div v-else>
                    <AddQuestion :selectedExam="selectedExam" :selectedQuestion="selectedQuestion"
                        @quizAdded="fetchExams" @cancel="showExamForm = true" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>