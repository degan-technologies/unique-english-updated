<template>
    <div class="w-full min-h-screen flex items-center justify-center p-6">
        <div
            class="w-full max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-2xl"
        >
            <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">
                Add New Exam
            </h1>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Exam Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700"
                        >Exam Title</label
                    >
                    <input
                        v-model="exam.title"
                        type="text"
                        placeholder="Enter exam title"
                        class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                    />
                    <p v-if="errors.title" class="text-red-500 text-sm mt-1">
                        {{ errors.title }}
                    </p>
                </div>

                <!-- Exam Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700"
                        >Description</label
                    >
                    <textarea
                        v-model="exam.description"
                        placeholder="Enter exam description"
                        class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                    ></textarea>
                    <p
                        v-if="errors.description"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.description }}
                    </p>
                </div>

                <!-- Questions Section -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-700">
                        Questions
                    </h2>
                    <div
                        v-for="(question, qIndex) in exam.questions"
                        :key="qIndex"
                        class="p-4 border rounded-lg bg-gray-50"
                    >
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Question {{ qIndex + 1 }}
                        </label>
                        <input
                            v-model="question.text"
                            type="text"
                            placeholder="Enter question"
                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                        <p
                            v-if="errors[`question_${qIndex}`]"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors[`question_${qIndex}`] }}
                        </p>

                        <label
                            class="block text-sm font-semibold text-gray-700 mt-4"
                            >Options</label
                        >
                        <div
                            v-for="(option, oIndex) in question.options"
                            :key="oIndex"
                            class="flex items-center mt-2"
                        >
                            <input
                                v-model="question.options[oIndex]"
                                type="text"
                                placeholder="Option {{ oIndex + 1 }}"
                                class="w-full p-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            />
                            <button
                                @click.prevent="removeOption(qIndex, oIndex)"
                                class="ml-2 text-red-500 hover:text-red-700"
                            >
                                Remove
                            </button>
                        </div>
                        <p
                            v-if="errors[`options_${qIndex}`]"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors[`options_${qIndex}`] }}
                        </p>

                        <button
                            @click.prevent="addOption(qIndex)"
                            class="mt-2 text-lime-600 hover:text-lime-800"
                        >
                            + Add Option
                        </button>

                        <label
                            class="block text-sm font-semibold text-gray-700 mt-4"
                            >Correct Answer</label
                        >
                        <select
                            v-model="question.correctAnswer"
                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            <option
                                v-for="(option, oIndex) in question.options"
                                :key="oIndex"
                                :value="oIndex"
                            >
                                {{ option }}
                            </option>
                        </select>
                        <p
                            v-if="errors[`correctAnswer_${qIndex}`]"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors[`correctAnswer_${qIndex}`] }}
                        </p>

                        <button
                            @click.prevent="removeQuestion(qIndex)"
                            class="mt-4 text-red-600 hover:text-red-800"
                        >
                            Remove Question
                        </button>
                    </div>

                    <button
                        @click.prevent="addQuestion"
                        class="text-lime-600 hover:text-lime-800"
                    >
                        + Add Question
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition-all"
                    >
                        Save Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const exam = ref({
    title: "",
    description: "",
    questions: [{ text: "", options: ["", "", ""], correctAnswer: null }],
});

const errors = ref({});

const addQuestion = () => {
    exam.value.questions.push({
        text: "",
        options: ["", "", ""],
        correctAnswer: null,
    });
};

const removeQuestion = (index) => {
    exam.value.questions.splice(index, 1);
};

const addOption = (qIndex) => {
    exam.value.questions[qIndex].options.push("");
};

const removeOption = (qIndex, oIndex) => {
    exam.value.questions[qIndex].options.splice(oIndex, 1);
};

const validateForm = () => {
    errors.value = {};
    let valid = true;

    if (!exam.value.title.trim()) {
        errors.value.title = "Exam title is required.";
        valid = false;
    }
    if (!exam.value.description.trim()) {
        errors.value.description = "Exam description is required.";
        valid = false;
    }

    exam.value.questions.forEach((question, qIndex) => {
        if (!question.text.trim()) {
            errors.value[`question_${qIndex}`] = "Question text is required.";
            valid = false;
        }

        if (question.options.length < 2) {
            errors.value[`options_${qIndex}`] =
                "At least two options are required.";
            valid = false;
        }

        if (question.correctAnswer === null || question.correctAnswer === "") {
            errors.value[`correctAnswer_${qIndex}`] =
                "A correct answer must be selected.";
            valid = false;
        }
    });

    return valid;
};

const handleSubmit = () => {
    if (!validateForm()) return;

    console.log("Exam Added:", exam.value);
    alert("Exam saved successfully!");

    exam.value = {
        title: "",
        description: "",
        questions: [{ text: "", options: ["", "", ""], correctAnswer: null }],
    };
};
</script>

<style scoped>
/* Add any additional styling if needed */
</style>
