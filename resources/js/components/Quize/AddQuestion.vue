<template>
    <div class="max-w-xl mx-auto p-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            {{ selectedQuestion ? "Update Question" : "Add Question" }}
        </h2>
        <form class="space-y-6">
            <!-- Question Field -->
            <div>
                <label class="block text-base font-medium text-gray-700 mb-1">
                    Question
                </label>
                <textarea
                    v-model.trim="quizForm.question"
                    placeholder="Enter test question"
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500 text-base"
                ></textarea>
            </div>
            <!-- Hint Field -->
            <div>
                <label class="block text-base font-medium text-gray-700 mb-1">
                    Hint (optional)
                </label>
                <textarea
                    v-model.trim="quizForm.hint"
                    placeholder="Add hint here (at least 10 characters)"
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500 text-base"
                ></textarea>
            </div>
            <!-- Choices Section -->
            <div>
                <label class="block text-base font-medium text-gray-700 mb-1">
                    Choices
                    <span
                        @click="addChoice"
                        class="cursor-pointer ml-2 text-lime-600 hover:text-lime-500"
                    >
                        <i class="fa-solid fa-plus text-lg"></i>
                    </span>
                </label>
                <div class="space-y-3 mt-2">
                    <div
                        v-for="(choice, index) in quizForm.choice"
                        :key="index"
                        class="flex items-center space-x-3"
                    >
                        <input
                            type="radio"
                            v-model="quizForm.answer"
                            :value="choice"
                            name="choice"
                            class="w-5 h-5 text-lime-600"
                        />
                        <input
                            v-model.trim="quizForm.choice[index]"
                            type="text"
                            placeholder="Enter choice"
                            class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500 text-base"
                        />
                        <button
                            type="button"
                            @click="removeChoice(index)"
                            class="text-red-500 hover:text-red-700"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Submit Button -->
            <button
                type="button"
                @click="selectedQuestion ? updateQuiz() : storeQuiz()"
                class="w-full py-3 bg-lime-600 text-white font-semibold rounded-lg hover:bg-lime-700 transition duration-200 text-base"
            >
                {{ selectedQuestion ? "Update Question" : "Add Question" }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["quizAdded"]);

const props = defineProps({
    selectedExam: Object,
    selectedQuestion: {
        type: Object,
        default: null,
    },
});

const quizForm = ref({
    question: "",
    choice: ["", "", "", ""],
    hint: "",
    question_type: "choice",
    answer: null,
});

// Populate or reset the form when selectedQuestion changes.
watch(
    () => props.selectedQuestion,
    (newVal) => {
        if (newVal) {
            const existing = { ...newVal };
            quizForm.value = {
                question: existing.question,
                choice:
                    existing.choice && existing.choice.length
                        ? existing.choice
                        : ["", "", "", ""],
                hint: existing.hint || "",
                question_type: existing.question_type || "choice",
                answer: existing.answer || null,
            };
        } else {
            quizForm.value = {
                question: "",
                choice: ["", "", "", ""],
                hint: "",
                question_type: "choice",
                answer: null,
            };
        }
    },
    { immediate: true }
);

const addChoice = () => {
    if (quizForm.value.choice.length < 6) {
        quizForm.value.choice.push("");
    }
};

function removeChoice(index) {
    const removedChoice = quizForm.value.choice[index];
    quizForm.value.choice.splice(index, 1);
    if (quizForm.value.answer === removedChoice) {
        quizForm.value.answer = null;
    }
}

function storeQuiz() {
    if (!quizForm.value.question.trim()) {
        toast.error("Question is required");
        return;
    }
    const choices = quizForm.value.choice.filter((c) => c.trim() !== "");
    if (choices.length < 2) {
        toast.error("At least two choices are required");
        return;
    }
    if (!quizForm.value.answer) {
        toast.error("Please select the correct answer");
        return;
    }
    if (quizForm.value.hint.trim().length < 10) {
        toast.error("Hint must be at least 10 characters");
        return;
    }
    const payload = {
        question: quizForm.value.question,
        choice: choices,
        answer: quizForm.value.answer,
        question_type: quizForm.value.question_type,
        exam_id: props.selectedExam.id,
        hint: quizForm.value.hint,
    };
    Axios.post("/api/quize", payload)
        .then(() => {
            toast.success("Quiz created successfully");
            emit("quizAdded");
            quizForm.value = {
                question: "",
                choice: ["", "", "", ""],
                hint: "",
                question_type: "choice",
                answer: null,
            };
        })
        .catch(() => {
            toast.error("Failed to create quiz");
        });
}

function updateQuiz() {
    if (!props.selectedQuestion) return;
    if (!quizForm.value.question.trim()) {
        toast.error("Question is required");
        return;
    }
    const choices = quizForm.value.choice.filter((c) => c.trim() !== "");
    if (choices.length < 2) {
        toast.error("At least two choices are required");
        return;
    }
    if (!quizForm.value.answer) {
        toast.error("Please select the correct answer");
        return;
    }
    if (quizForm.value.hint.trim().length < 10) {
        toast.error("Hint must be at least 10 characters");
        return;
    }
    const payload = {
        question: quizForm.value.question,
        choice: choices,
        answer: quizForm.value.answer,
        hint: quizForm.value.hint,
    };
    Axios.patch(`/api/quize/${props.selectedQuestion.id}`, payload)
        .then(() => {
            toast.success("Quiz updated successfully");
        })
        .catch(() => {
            toast.error("Failed to update quiz");
        });
}
</script>
