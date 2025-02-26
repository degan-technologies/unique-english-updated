<template>
    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Manage Exam Questions
        </h2>

        <!-- Responsive Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse mb-6">
                <thead>
                    <tr class="bg-lime-700 text-white">
                        <th class="px-4 md:px-6 py-3 text-left">Question</th>
                        <th class="px-4 md:px-6 py-3 text-left">Choices</th>
                        <th class="px-4 md:px-6 py-3 text-left">
                            Correct Answer
                        </th>
                        <th class="px-4 md:px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(question, index) in exam.questions"
                        :key="index"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="px-4 md:px-6 py-4 text-sm md:text-base">
                            {{ question.text }}
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            <ul class="text-sm md:text-base">
                                <li
                                    v-for="(option, oIndex) in question.options"
                                    :key="oIndex"
                                >
                                    {{ option }}
                                </li>
                            </ul>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-sm md:text-base">
                            {{
                                question.options[question.correctAnswer] ||
                                "Not Set"
                            }}
                        </td>
                        <td class="px-4 md:px-6 py-4 text-center">
                            <button
                                @click="editQuestion(index)"
                                class="text-blue-500 hover:text-blue-700 mr-2"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button
                                @click="deleteQuestion(index)"
                                class="text-red-500 hover:text-red-700"
                            >
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Responsive & Scrollable Edit Question Modal -->
        <div
            v-if="isEditing"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg md:max-w-xl max-h-[80vh] overflow-y-auto"
            >
                <h2 class="text-xl font-bold text-center text-lime-700 mb-4">
                    Edit Question
                </h2>
                <form @submit.prevent="saveEdit">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Question</label
                        >
                        <input
                            v-model="editableQuestion.text"
                            type="text"
                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                    </div>

                    <!-- Scrollable Choices Section -->
                    <div
                        class="mb-4 max-h-48 overflow-y-auto p-2 border rounded-lg"
                    >
                        <label class="block text-sm font-semibold text-gray-700"
                            >Choices</label
                        >
                        <div class="space-y-2 mt-2">
                            <div
                                v-for="(
                                    option, oIndex
                                ) in editableQuestion.options"
                                :key="oIndex"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    v-model="editableQuestion.options[oIndex]"
                                    type="text"
                                    class="flex-grow p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                                />
                                <button
                                    v-if="editableQuestion.options.length > 2"
                                    @click="removeOption(oIndex)"
                                    type="button"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addOption"
                            type="button"
                            class="mt-2 text-blue-600 hover:text-blue-800"
                        >
                            + Add Option
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Correct Answer</label
                        >
                        <select
                            v-model="editableQuestion.correctAnswer"
                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            <option
                                v-for="(
                                    option, oIndex
                                ) in editableQuestion.options"
                                :key="oIndex"
                                :value="oIndex"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <!-- Fixed Buttons at Bottom -->
                    <div
                        class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0"
                    >
                        <button
                            type="submit"
                            class="w-full md:w-auto px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            Save Changes
                        </button>
                        <button
                            @click="cancelEdit"
                            type="button"
                            class="w-full md:w-auto px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const exam = ref({
    questions: [
        {
            text: "What is the capital of France?",
            options: ["Paris", "Berlin", "London", "Madrid"],
            correctAnswer: 0,
        },
        {
            text: "What is 2 + 2?",
            options: ["3", "4", "5", "6"],
            correctAnswer: 1,
        },
    ],
});

const isEditing = ref(false);
const editableQuestion = ref({ text: "", options: [], correctAnswer: 0 });
const editingIndex = ref(null);

const editQuestion = (index) => {
    editingIndex.value = index;
    editableQuestion.value = JSON.parse(
        JSON.stringify(exam.value.questions[index])
    );
    isEditing.value = true;
};

const saveEdit = () => {
    if (editingIndex.value !== null) {
        exam.value.questions[editingIndex.value] = JSON.parse(
            JSON.stringify(editableQuestion.value)
        );
    }
    isEditing.value = false;
    editingIndex.value = null;
};

const cancelEdit = () => {
    isEditing.value = false;
    editingIndex.value = null;
};

const deleteQuestion = (index) => {
    if (confirm("Are you sure you want to delete this question?")) {
        exam.value.questions.splice(index, 1);
    }
};

const addOption = () => {
    if (!editableQuestion.value.options) {
        editableQuestion.value.options = [];
    }
    editableQuestion.value.options.push("");
};

const removeOption = (index) => {
    if (editableQuestion.value.options.length > 2) {
        editableQuestion.value.options.splice(index, 1);
    }
};
</script>

<style scoped>
/* Make modal scrollable */
.max-h-[80vh] {
    max-height: 80vh;
}
</style>
