<script setup>
import { ref, watch } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["quizAdded"]);

const quizForm = ref({
    question: "",
    choice: ["", "", "", ""],
    answer: "",
    question_type: "choice",
    hint: "",
});

const props = defineProps({
    selectedExam: Object,
    selectedQuestion: Object | null,
});

if (props.selectedQuestion) {
    quizForm.value = { ...props.selectedQuestion };
}

function addAnswer(answer) {
    quizForm.value.answer = answer;
}

const addChoice = () => {
    if (quizForm.value.choice.length < 6) {
        quizForm.value.choice.push("");
    }
};

function storeQuiz() {
    const payload = {
        question: quizForm.value.question,
        choice: quizForm.value.choice,
        answer: quizForm.value.answer,
        question_type: quizForm.value.question_type,
        exam_id: props.selectedExam.id,
        hint: quizForm.value.hint,
    };

    Axios.post("/api/quize", payload)
        .then((res) => {
            toast.success("Quiz added successfully");
            emit("quizAdded");
            quizForm.value = {
                question: "",
                choice: ["", "", "", ""],
                answer: "",
                question_type: "choice",
            };
        })
        .catch((error) => {
            toast.error("Failed to add quiz");
        });
}

function updateQuiz() {
    if (!props.selectedQuestion) return;

    const payload = {
        question: quizForm.value.question,
        choice: quizForm.value.choice,
        answer: quizForm.value.answer,
        hint: quizForm.value.hint,
    };

    Axios.patch(`/api/quize/${props.selectedQuestion.id}`, payload)
        .then((res) => {
            toast.success("Quiz added successfully");
        })
        .catch((error) => {
            toast.error("Failed to add quiz");
        });
}

watch(
    () => props.selectedQuestion,
    () => {
        quizForm.value = { ...props.selectedQuestion };
    }
);
</script>

<template>
    <div class="">
        <div class="col-span-3 bg-gray-50 p-4">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">
                        Question
                    </label>
                    <textarea
                        v-model.trim="quizForm.question"
                        placeholder="Enter test question"
                        class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-1 text-sm focus:ring-lime-700"
                    ></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">
                        Hint (optional)
                    </label>
                    <textarea
                        v-model.trim="quizForm.hint"
                        placeholder="Add hint here"
                        class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-1 text-sm focus:ring-lime-700"
                    ></textarea>
                </div>
                <div class="w-[90%]">
                    <label class="block text-sm font-semibold text-gray-700">
                        Choices
                        <span>
                            <i
                                class="fa-solid fa-plus text-xl text-lime-700 hover:text-lime-500 pl-2"
                            ></i>
                        </span>
                    </label>
                    <div class="space-y-2 mt-2">
                        <div
                            v-for="(choice, index) in quizForm.choice"
                            :key="index"
                            class="flex items-center space-x-2"
                        >
                            <input
                                type="radio"
                                @input="addAnswer(choice)"
                                name="choice"
                                class="w-5 h-5 p-2 text-sm border rounded-lg"
                            />
                            <input
                                v-model.trim="quizForm.choice[index]"
                                type="text"
                                class="w-full p-2 text-sm border rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                                placeholder="Enter choice"
                            />
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    @click="selectedQuestion ? updateQuiz() : storeQuiz()"
                    class="border p-2 mt-2 px-4 bg-lime-700 border-gray-400 text-white rounded-lg text-center hover:bg-lime-800 text-sm w-fit mx-auto"
                >
                    {{ selectedQuestion ? "Update Question" : "Add Question" }}
                </button>
            </form>
        </div>
    </div>
</template>
