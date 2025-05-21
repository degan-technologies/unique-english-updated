<script setup>
import { ref, computed, onMounted } from "vue";
import Axios from "axios";

import Spinner from "@/components/Layout/Spinner.vue";

const props = defineProps({
    courseId: String,
    selectedCourseSlug: String,
});

// State
const qaSections = ref([]);
const newQuestion = ref("");
const questionError = ref("");

const editableQa = ref({});
const replayQaId = ref(null);
const actionEditReplay = ref(false);
const updatedData = ref({
    question: null,
    qaSectionId: null,
    answer: null,
    answerId: null,
});

// UI Controls
const showAllQuestions = ref(false);
const showAllAnswers = ref({});
const isLoading = ref(false);

const showDeleteModal = ref(false);
const selectedData = ref(null);
const selectedType = ref(null);

// Computed
const displayedQuestions = computed(() => {
    return showAllQuestions.value ? qaSections.value : qaSections.value.slice(0, 2);
});

// Methods
const fetchQA = async () => {

    console.log("Fetching Q&A...");
    try {
        isLoading.value = true;
        const response = await Axios.get(`/api/get-course-qa/${props.selectedCourseSlug}`);
        qaSections.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch Q&A:", error);
        questionError.value = "Failed to load questions. Please try again.";
    } finally {
        isLoading.value = false;
    }
};

const submitQuestion = async () => {
    if (!newQuestion.value.trim()) {
        questionError.value = "Question cannot be empty";
        return;
    }

    try {
        const response = await Axios.post("/api/QASection", {
            question: newQuestion.value.trim(),
            course_id: props.courseId,
        });
        qaSections.value = [response.data.data, ...qaSections.value];
        newQuestion.value = "";
        questionError.value = "";
    } catch (error) {
        console.error("Failed to submit question:", error);
        questionError.value = "Failed to submit question. Please try again.";
    }
};

const updateQuestion = async () => {
    try {
         const response = await Axios.put(`/api/QASection/${editableQa.value.id}`, {
            question: editableQa.value.question,
        });
        editableQa.value = {};
        qaSections.value = qaSections.value.map(item=>item.id===response.data.data.id ? response.data.data: item);
    } catch (error) {
        console.error("Failed to update question:", error);
        questionError.value = "Failed to update question. Please try again.";
    }
};

const removeQuestion = async (id) => { 
    try {
        await Axios.delete(`/api/QASection/${id}`);
        qaSections.value = qaSections.value.filter(q => q.id !== id);
        showDeleteModal.value = false;
        selectedData.value = null;
    } catch (error) {
        console.error("Failed to delete question:", error);
        questionError.value = "Failed to delete question. Please try again.";
    }
};

const submitAnswer = async (questionId) => {
    try {
        const payload = {
            answer: updatedData.value.answer,
            question_id: questionId,
        };

        if (actionEditReplay.value) {
            await Axios.put(`/api/answers/${updatedData.value.answerId}`, payload);
        } else {
            await Axios.post("/api/answers", payload);
        }

        // Reset form and refresh data
        updatedData.value.answer = "";
        replayQaId.value = null;
        actionEditReplay.value = false;
        await fetchQA();
    } catch (error) {
        console.error("Failed to submit answer:", error);
        questionError.value = "Failed to submit answer. Please try again.";
    }
};

const removeAnswer = async (answerId) => { 
    try {
        await Axios.delete(`/api/answers/${answerId}`);
        await fetchQA();
        showDeleteModal.value = false;
        selectedData.value = null;
    } catch (error) { 
        questionError.value = "Failed to delete answer. Please try again.";
    }
};

const editAnswer = (answer) => {
    replayQaId.value = answer.question_id;
    updatedData.value.answer = answer.answer;
    updatedData.value.answerId = answer.id;
    actionEditReplay.value = true;
};
function openModal(selectdeTodelete, type) { 
    selectedData.value = selectdeTodelete;
    selectedType.value = type;
    showDeleteModal.value = true;
}
const closeModal = () => {
    showDeleteModal.value = false;
    selectedData.value = null;
};

const toggleAnswerMode = (qa) => {
    if (replayQaId.value === qa.id) {
        replayQaId.value = null;
        actionEditReplay.value = false;
        updatedData.value.answer = "";
    } else {
        replayQaId.value = qa.id;
        updatedData.value.answer = "";
        actionEditReplay.value = false;
    }
};

const getDisplayedAnswers = (answers, questionId) => {
    return showAllAnswers.value[questionId] ? answers : answers?.slice(0, 2) || [];
};

const toggleShowAnswers = (questionId) => {
    showAllAnswers.value = {
        ...showAllAnswers.value,
        [questionId]: !showAllAnswers.value[questionId]
    };
};

// Lifecycle
onMounted(fetchQA);
</script>

<template>
    <div class="w-full mx-auto p-2">
        <!-- Error Message -->
        <div v-if="questionError" class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
            {{ questionError }}
        </div>

        <!-- Ask Question Section -->
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Ask a Question</h2>
            <div class="flex gap-3">
                <div class="flex-grow">
                    <textarea v-model="newQuestion" rows="3"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-transparent transition"
                        placeholder="What would you like to ask?" :disabled="isLoading"></textarea>
                </div>
                <button @click="submitQuestion" class="self-start p-3 text-lime-600 hover:text-lime-700 transition"
                    :disabled="isLoading" aria-label="Submit question">
                    <i class="fa-solid fa-paper-plane text-2xl"></i>
                </button>
            </div>
        </section>

        <!-- Questions List -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Questions</h2>
            <div v-if="isLoading && !qaSections.length" class="text-center py-8">
                <Spinner/>
            </div>

            <div v-else-if="!qaSections.length" class="text-center py-8 text-gray-500">
                No questions yet. Be the first to ask!
            </div>

            <div v-else class="space-y-6">
                <!-- Question Cards -->
                <article v-for="qa in displayedQuestions" :key="qa.id"
                    class="bg-white p-5 rounded-lg shadow border border-gray-200">
                    <!-- Question Header -->
                    <header class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100">
                            <img v-if="qa.user?.profile" :src="qa.user.profile" :alt="qa.user.full_name"
                                class="w-full h-full object-cover" />
                            <i v-else class="fas fa-user-circle text-lime-500 text-2xl"></i>
                        </div>
                        <span class="font-medium text-gray-800">
                            {{ qa.user?.full_name || "Anonymous" }}
                        </span>
                        <span class="text-sm text-gray-500 ml-auto">
                            {{ new Date(qa.created_at).toLocaleDateString() }}
                        </span>
                    </header>

                    <!-- Question Content -->
                    <div class="mb-4">
                        <p v-if="editableQa.id !== qa.id" class="text-gray-700">
                            {{ qa.question }}
                        </p>

                        <!-- Edit Question Form -->
                        <div v-else class="flex gap-2">
                            <textarea v-model="editableQa.question" rows="2"
                                class="flex-grow p-2 border rounded-lg focus:ring-2 focus:ring-lime-400"></textarea>
                            <button @click="updateQuestion" class="self-start p-2 text-lime-600 hover:text-lime-700"
                                aria-label="Save changes">
                                <i class="fa-solid fa-check text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Question Actions -->
                    <div class="flex gap-4 text-sm">
                        <button v-if="qa.editable && editableQa.id !== qa.id"
                            @click="editableQa = { id: qa.id, question: qa.question }"
                            class="text-blue-600 hover:underline">
                            Edit
                        </button>
                        <button v-if="qa.editable" @click="openModal(qa, 'question')" class="text-red-600 hover:underline">
                            Delete
                        </button>
                        <button @click="toggleAnswerMode(qa)" class="text-lime-600 hover:underline">
                            {{ replayQaId === qa.id ? 'Cancel' : 'Answer' }}
                        </button>
                    </div>

                    <!-- Answer Form -->
                    <div v-if="replayQaId === qa.id" class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex gap-2">
                            <textarea v-model="updatedData.answer" rows="3"
                                class="flex-grow p-3 border rounded-lg focus:ring-2 focus:ring-lime-400"
                                placeholder="Write your answer..."></textarea>
                            <button @click="submitAnswer(qa.id)"
                                class="self-start p-3 text-lime-600 hover:text-lime-700" aria-label="Submit answer">
                                <i class="fa-solid fa-paper-plane text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Answers List -->
                    <div v-if="qa.answers?.length" class="mt-4 space-y-3">
                        <div v-for="answer in getDisplayedAnswers(qa.answers, qa.id)" :key="answer.id"
                            class="bg-gray-50 p-3 rounded-lg border-l-4 border-lime-400">
                            <!-- Answer Header -->
                            <header class="flex items-center gap-3 mb-2">
                                <div class="w-6 h-6 rounded-full overflow-hidden bg-gray-100">
                                    <img v-if="answer.user?.profile" :src="answer.user.profile"
                                        :alt="answer.user.full_name" class="w-full h-full object-cover" />
                                    <i v-else class="fas fa-user-circle text-lime-500 text-xl"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-800">
                                    {{ answer.user?.full_name || "Anonymous" }}
                                </span>
                                <span class="text-xs text-gray-500 ml-auto">
                                    {{ new Date(answer.created_at).toLocaleDateString() }}
                                </span>
                            </header>

                            <!-- Answer Content -->
                            <p class="text-gray-700 text-sm">
                                {{ answer.answer }}
                            </p>

                            <!-- Answer Actions -->
                            <div v-if="answer.editable" class="flex gap-3 mt-2 text-xs">
                                <button @click="editAnswer(answer)" class="text-blue-600 hover:underline">
                                    Edit
                                </button>
                                <button @click="openModal(answer, 'answer')" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Show More Answers Button -->
                        <button v-if="qa.answers.length > 2" @click="toggleShowAnswers(qa.id)"
                            class="mt-2 text-sm text-lime-600 hover:underline">
                            {{ showAllAnswers[qa.id] ? 'Show fewer answers' : `Show all answers (${qa.answers.length})`
                            }}
                        </button>
                    </div>
                </article>

                <!-- Show More Questions Button -->
                <button v-if="qaSections.length > 2" @click="showAllQuestions = !showAllQuestions"
                    class="w-full py-2 bg-lime-600 hover:bg-lime-700 text-white rounded-lg transition">
                    {{ showAllQuestions ? 'Show fewer questions' : `Show all questions (${qaSections.length})` }}
                </button>
            </div>
        </section>

    </div>
        <!-- Delete Confirmation Modal -->
    <transition name="fade">
        <div v-if="showDeleteModal && selectedData"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white rounded shadow-lg w-96 p-6">
                <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
                <p class="mb-6">
                    Are you sure you want to delete ?
                </p>
                <div class="flex justify-end space-x-2">
                    <button @click="closeModal" class="px-4 py-3 border rounded hover:bg-gray-100">
                        Cancel
                    </button>
                    <button @click="selectedType == 'answer' ? removeAnswer(selectedData?.id) : removeQuestion(selectedData?.id)   " class="px-4 py-3 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </transition> 
</template>

<style scoped>
textarea {
    resize: none;
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>