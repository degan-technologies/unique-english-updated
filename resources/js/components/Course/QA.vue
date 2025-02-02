<script setup>
    import { ref, computed } from "vue";

    // State for Q&A Section
    const questions = ref([]); // Always initialize as an array
    const newQuestion = ref("");
    const currentUser = "User123"; // Simulate current user
    const questionsToShow = ref(1); // Initially show only 1 question

    // Add a New Question
    const addQuestion = () => {
        if (newQuestion.value.trim() === "") return;
        questions.value.push({
            question: newQuestion.value.trim(),
            answers: [], // Always initialize as an array
            newAnswer: "",
            showReplyField: false,
            user: currentUser,
        });
        newQuestion.value = "";
    };

    // Toggle Reply Field
    const toggleReplyField = (index) => {
        if (questions.value[index]) {
            questions.value[index].showReplyField =
                !questions.value[index].showReplyField;
        }
    };

    // Add an Answer
    const addAnswer = (index) => {
        const qa = questions.value[index];
        if (qa && qa.newAnswer.trim() !== "") {
            qa.answers.push({
                text: qa.newAnswer.trim(),
                user: currentUser,
                likes: 0, // Initialize likes
            });
            qa.newAnswer = "";
            qa.showReplyField = false;
        }
    };

    // Like an Answer
    const likeAnswer = (questionIndex, answerIndex) => {
        const qa = questions.value[questionIndex];
        if (qa && qa.answers[answerIndex]) {
            qa.answers[answerIndex].likes++;
        }
    };

    // Load More Questions
    const loadMoreQuestions = () => {
        questionsToShow.value += 1; // Increment visible questions by 1
    };

    // Show Less Questions
    const showLessQuestions = () => {
        questionsToShow.value = 1; // Reset visible questions to 1
    };

    // Computed: Questions to Display
    const visibleQuestions = computed(() =>
        questions.value.slice(0, questionsToShow.value)
    );
</script>

<template>
    <div class="mt-2 w-full lg:w-full mx-auto" >
        <!-- Ask a Question -->
        <div class="mb-8">
            <input
                v-model="newQuestion"
                type="text"
                class="w-full p-3 border border-gray-300 bg-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400 mb-4 transition duration-300"
                placeholder="Ask a question..."
            />
            <button
                @click="addQuestion"
                class="bg-lime-500 text-white px-6 py-3 rounded-lg shadow hover:bg-lime-600 transition duration-300 w-full lg:w-auto"
            >
                Submit Question
            </button>
        </div>

        <!-- List Questions and Answers -->
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800">Comments</h2>
            <div class="space-y-6">
                <div
                    v-for="(qa, index) in visibleQuestions"
                    :key="index"
                    class="mb-6 border-b border-gray-200 pb-4" >
                    <!-- Question -->
                    <p class="text-lg font-semibold text-gray-800 mb-2">
                        <i class="fas fa-user-circle text-lime-500 mr-2"></i>
                        {{ qa.user }}: {{ qa.question }}
                    </p>

                    <!-- Answers -->
                    <div
                        v-if="qa.answers && qa.answers.length > 0"
                        class="ml-4 mt-3 space-y-2 max-h-32 overflow-y-auto"
                    >
                        <div
                            v-for="(answer, aIndex) in qa.answers"
                            :key="aIndex"
                            class="flex items-start space-x-2"
                        >
                            <i class="fas fa-user-circle text-gray-500 mr-2"></i>
                            <div>
                                <div
                                    class="bg-lime-700 text-gray-800 p-3 rounded-lg shadow-md transition-transform duration-300 ease-in-out"
                                >
                                    {{ answer.text }}
                                </div>
                                <button
                                    @click="likeAnswer(index, aIndex)"
                                    class="text-lime-500 ml-2 mt-1 hover:text-lime-700 transition duration-200"
                                >
                                    👍 {{ answer.likes }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Section -->
                    <div class="mt-4 flex items-start space-x-2">
                        <button
                            @click="toggleReplyField(index)"
                            class="text-lime-500 bg-lime-100 px-4 py-2 rounded-full shadow-md hover:bg-lime-200 transition duration-300"
                        >
                            <span v-if="!qa.showReplyField">Reply</span>
                            <span v-else>Cancel</span>
                        </button>

                        <!-- Reply Input -->
                        <div v-if="qa.showReplyField" class="flex-1">
                            <div class="mt-2">
                                <input
                                    v-model="qa.newAnswer"
                                    type="text"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400 transition duration-300"
                                    placeholder="Write your answer..."
                                />
                                <button
                                    @click="addAnswer(index)"
                                    class="bg-lime-500 text-white px-6 py-3 rounded-lg shadow hover:bg-lime-600 transition duration-300 mt-3 w-full"
                                >
                                    Submit Answer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- See More / Show Less Buttons -->
        <div class="mt-4 text-center">
            <div class="space-x-4">
                <button
                    v-if="visibleQuestions.length < questions.length"
                    @click="loadMoreQuestions"
                    class="bg-gray-200 text-lime-500 px-6 py-3 rounded-lg shadow-md hover:bg-gray-300 transition duration-300"
                >
                    <i class="fas fa-chevron-down mr-2"></i>See More
                </button>

                <button
                    v-if="visibleQuestions.length > 1"
                    @click="showLessQuestions"
                    class="bg-gray-200 text-lime-500 px-6 py-3 rounded-lg shadow-md hover:bg-gray-300 transition duration-300"
                >
                    <i class="fas fa-chevron-up mr-2"></i>Show Less
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Tailwind Styles */
.bg-lime-500 {
    background-color: #84cc16;
}

.bg-lime-600 {
    background-color: #65a30d;
}

.bg-lime-100 {
    background-color: #f0fdf4;
}

.bg-gray-200 {
    background-color: #e5e7eb;
}

.text-lime-500 {
    color: #84cc16;
}

.text-lime-700 {
    color: #4d7c0f;
}

.shadow-md {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.shadow-lg {
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.hover\:bg-lime-200:hover {
    background-color: #d9f99d;
}

.hover\:bg-gray-300:hover {
    background-color: #d1d5db;
}

.transition {
    transition: all 0.3s ease-in-out;
}

.space-x-4 {
    display: flex;
    gap: 1rem;
}
</style>
