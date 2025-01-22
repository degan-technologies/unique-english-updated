<template>
    <div
        class="course-details-container p-6 max-w-screen-xl mx-auto bg-white rounded-lg shadow-lg h-screen overflow-x-hidden overflow-y-auto"
    >
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">
                The Art of Filmmaking: Mastering Storytelling and Editing with
                Adobe Premiere Pro
            </h1>
            <p class="text-lg text-gray-700 mt-2">
                A course by <strong>Robel Birhanu</strong>
            </p>
            <div class="overflow-hidden max-w-full h-auto rounded-lg my-6">
                <img
                    src="/images/course-1.jpg"
                    alt="Course Image"
                    class="w-full h-auto rounded-lg object-cover transform hover:scale-105 transition-transform duration-300 shadow-lg hover:shadow-xl"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
            <!-- Left Card: Course Content -->
            <div class="bg-white rounded-lg shadow-lg pb-12">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">
                            What You Will Learn
                        </h2>
                        <!-- Global Progress Circle -->
                        <div class="relative">
                            <div
                                class="w-16 h-16 mx-auto rounded-full border-4 border-blue-500 flex items-center justify-center bg-blue-100 shadow-md"
                            >
                                <span class="text-lg font-bold text-blue-500">
                                    {{ calculateGlobalProgress() }}%
                                </span>
                            </div>
                            <p class="text-center text-sm mt-2 text-gray-700">
                                Global Progress:
                                {{ calculateGlobalProgress() }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div
                        v-for="(module, moduleIndex) in modules"
                        :key="moduleIndex"
                        class="mb-6"
                    >
                        <div class="flex justify-between items-center">
                            <button
                                @click="toggleLesson(moduleIndex)"
                                class="text-blue-500 hover:text-blue-700 text-xl w-full text-left py-4 px-4 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300"
                            >
                                <span class="font-semibold">{{
                                    module.title
                                }}</span>
                                <i
                                    :class="[
                                        { 'rotate-180': module.showLessons },
                                        'transition-transform duration-300',
                                    ]"
                                    >&#x25BC;</i
                                >
                            </button>
                        </div>

                        <div v-if="module.showLessons" class="ml-4 mt-2">
                            <ul class="list-none pl-0">
                                <li
                                    v-for="(
                                        lesson, lessonIndex
                                    ) in module.lessons"
                                    :key="lessonIndex"
                                    class="text-gray-700 flex items-center my-1"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            module.completedLessons.includes(
                                                lessonIndex
                                            )
                                        "
                                        @change="
                                            toggleLessonCompletion(
                                                moduleIndex,
                                                lessonIndex
                                            )
                                        "
                                        class="mr-2"
                                    />
                                    {{ lesson }}
                                </li>
                            </ul>

                            <!-- Start Quiz Button -->
                            <div
                                class="mt-8"
                                v-if="
                                    module.completedLessons.length ===
                                    module.lessons.length
                                "
                            >
                                <button
                                    @click="startQuiz(moduleIndex)"
                                    class="bg-green-500 text-white px-6 py-2 rounded-full hover:bg-green-600 transition-colors"
                                >
                                    Start Quiz
                                </button>
                            </div>

                            <!-- Quiz Section -->
                            <div v-if="module.quizStarted">
                                <h2 class="text-2xl font-bold mb-4">
                                    Quiz for {{ module.title }}
                                </h2>
                                <div class="mb-6">
                                    <p class="text-lg font-semibold mb-4">
                                        {{
                                            module.quiz[
                                                currentQuestion[moduleIndex]
                                            ].text
                                        }}
                                    </p>
                                    <div class="flex flex-col gap-2">
                                        <label
                                            v-for="(option, optIdx) in module
                                                .quiz[
                                                currentQuestion[moduleIndex]
                                            ].options"
                                            :key="optIdx"
                                            class="flex items-center gap-2"
                                        >
                                            <input
                                                type="radio"
                                                :name="
                                                    moduleIndex +
                                                    '-question' +
                                                    currentQuestion[moduleIndex]
                                                "
                                                :value="option"
                                                v-model="
                                                    module.quiz[
                                                        currentQuestion[
                                                            moduleIndex
                                                        ]
                                                    ].selected
                                                "
                                                class="form-radio text-blue-500"
                                            />
                                            {{ option }}
                                        </label>
                                    </div>
                                </div>
                                <!-- Prev and Next Buttons -->
                                <div class="flex justify-between">
                                    <button
                                        v-if="currentQuestion[moduleIndex] > 0"
                                        @click="prevQuestion(moduleIndex)"
                                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                                    >
                                        Prev
                                    </button>
                                    <button
                                        v-if="
                                            currentQuestion[moduleIndex] <
                                            module.quiz.length - 1
                                        "
                                        @click="nextQuestion(moduleIndex)"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                    >
                                        Next
                                    </button>
                                    <button
                                        v-else
                                        @click="submitQuiz(moduleIndex)"
                                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors"
                                    >
                                        Submit
                                    </button>
                                </div>
                                <!-- Display Result -->
                                <div
                                    v-if="quizSubmitted[moduleIndex]"
                                    class="mt-4"
                                >
                                    <p class="text-xl font-semibold">
                                        Your Score:
                                        {{ calculateScore(moduleIndex) }}
                                        %
                                    </p>
                                    <button
                                        v-if="calculateScore(moduleIndex) < 75"
                                        @click="retakeQuiz(moduleIndex)"
                                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors mt-4"
                                    >
                                        Retake Quiz
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Course Details -->
            <div
                class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-0 h-fit"
            >
                <button
                    class="bg-green-500 text-white px-6 py-2 rounded-full mb-4 hover:bg-green-600 transition-colors w-full"
                >
                    Enroll Now
                </button>
                <h2 class="text-xl font-semibold mb-4">Course Details</h2>
                <div class="flex flex-col gap-4">
                    <div>
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                        <strong>Level:</strong> Beginners
                    </div>
                    <div>
                        <i class="fas fa-language text-green-500 mr-2"></i>
                        <strong>Language:</strong> Amharic
                    </div>
                    <div>
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        <strong>Duration:</strong> 5:02
                    </div>
                    <div>
                        <i class="fas fa-tasks text-red-500 mr-2"></i>
                        <strong>Activities:</strong> 30
                    </div>
                    <div>
                        <i class="fas fa-tv text-purple-500 mr-2"></i>
                        <strong>Access on:</strong> Mobile, Desktop, and TV
                    </div>
                    <div>
                        <i class="fas fa-users text-indigo-500 mr-2"></i>
                        <strong>Lifetime access to the community</strong>
                    </div>
                    <div>
                        <i class="fas fa-certificate text-teal-500 mr-2"></i>
                        <strong>Certificate of completion</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Modules with lessons and quizzes
const modules = ref([
    {
        title: "Module 1: Introduction to Filmmaking",
        showLessons: false,
        lessons: [
            "Lesson 1: What is Filmmaking?",
            "Lesson 2: History of Filmmaking",
            "Lesson 3: Filmmaking Techniques",
        ],
        completedLessons: [],
        quizStarted: false,
        quiz: [
            {
                text: "What is the primary goal of filmmaking?",
                options: ["To entertain", "To make money", "Both of these"],
                correct: "Both of these",
                selected: null,
            },
            {
                text: "Which of these is a filmmaking technique?",
                options: ["Camera angles", "Color grading", "Both of these"],
                correct: "Both of these",
                selected: null,
            },
        ],
    },
    {
        title: "Module 2: Adobe Premiere Pro Basics",
        showLessons: false,
        lessons: [
            "Lesson 1: Introduction to Adobe Premiere Pro",
            "Lesson 2: Working with the Timeline",
            "Lesson 3: Editing Basics",
        ],
        completedLessons: [],
        quizStarted: false,
        quiz: [
            {
                text: "What is Adobe Premiere Pro?",
                options: [
                    "A photo editing tool",
                    "A video editing tool",
                    "None of these",
                ],
                correct: "A video editing tool",
                selected: null,
            },
            {
                text: "Which panel is used for timeline editing?",
                options: ["Timeline Panel", "Effects Panel", "Both of these"],
                correct: "Timeline Panel",
                selected: null,
            },
        ],
    },
]);

// Reactive state for quizzes
const currentQuestion = ref([]);
const quizSubmitted = ref([]);

modules.value.forEach(() => {
    currentQuestion.value.push(0);
    quizSubmitted.value.push(false);
});

// Function to toggle lessons
const toggleLesson = (moduleIndex) => {
    modules.value[moduleIndex].showLessons =
        !modules.value[moduleIndex].showLessons;
};

// Function to toggle lesson completion
const toggleLessonCompletion = (moduleIndex, lessonIndex) => {
    const module = modules.value[moduleIndex];
    if (module.completedLessons.includes(lessonIndex)) {
        module.completedLessons = module.completedLessons.filter(
            (idx) => idx !== lessonIndex
        );
    } else {
        module.completedLessons.push(lessonIndex);
    }
};

// Function to start quiz
const startQuiz = (moduleIndex) => {
    modules.value[moduleIndex].quizStarted = true;
};

// Functions to navigate quiz questions
const nextQuestion = (moduleIndex) => {
    currentQuestion.value[moduleIndex]++;
};
const prevQuestion = (moduleIndex) => {
    currentQuestion.value[moduleIndex]--;
};

// Function to submit quiz
const submitQuiz = (moduleIndex) => {
    quizSubmitted.value[moduleIndex] = true;
};

// Function to calculate score
const calculateScore = (moduleIndex) => {
    const quiz = modules.value[moduleIndex].quiz;
    const correctAnswers = quiz.filter((q) => q.selected === q.correct).length;
    return Math.round((correctAnswers / quiz.length) * 100);
};

// Function to retake quiz
const retakeQuiz = (moduleIndex) => {
    const module = modules.value[moduleIndex];
    module.quiz.forEach((q) => (q.selected = null));
    currentQuestion.value[moduleIndex] = 0;
    quizSubmitted.value[moduleIndex] = false;
};

// Function to calculate global progress
const calculateGlobalProgress = () => {
    const totalLessons = modules.value.reduce(
        (sum, module) => sum + module.lessons.length,
        0
    );
    const completedLessons = modules.value.reduce(
        (sum, module) => sum + module.completedLessons.length,
        0
    );
    return Math.round((completedLessons / totalLessons) * 100);
};
</script>

<style scoped>
.progress-circle {
    background-color: #f3f4f6;
    border: 4px solid #3b82f6;
    color: #2563eb;
}
</style>



 <div>
        <div
            class="mt-10 w-4/5 bg-white bg-opacity-95 p-6 rounded-lg shadow-lg"
        >
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Q&A Section</h2>

            <!-- Ask a Question -->
            <div class="mb-8">
                <input
                    v-model="newQuestion"
                    type="text"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 mb-4"
                    placeholder="Ask a question..."
                />
                <button
                    @click="addQuestion"
                    class="bg-green-500 text-white px-6 py-2 rounded-lg shadow hover:bg-green-600 transition"
                >
                    Submit Question
                </button>
            </div>

            <!-- List Questions and Answers -->
            <div
                v-for="(qa, index) in questions"
                :key="index"
                class="mb-6 border-b border-gray-200 pb-4"
            >
                <!-- Display Question -->
                <p class="text-lg font-semibold text-gray-800">
                    {{ qa.question }}
                </p>

                <!-- List of Answers -->
                <div class="ml-4 mt-3">
                    <p
                        v-for="(answer, aIndex) in qa.answers"
                        :key="aIndex"
                        class="text-gray-700 bg-gray-100 p-2 rounded-md mb-2"
                    >
                        - {{ answer }}
                    </p>
                </div>

                <!-- Reply Section -->
                <div class="mt-3">
                    <button
                        @click="toggleReplyField(index)"
                        class="text-blue-500 underline hover:text-blue-700"
                    >
                        Reply
                    </button>

                    <!-- Reply Input -->
                    <div v-if="qa.showReplyField" class="mt-3">
                        <input
                            v-model="qa.newAnswer"
                            type="text"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 mb-3"
                            placeholder="Write your answer..."
                        />
                        <button
                            @click="addAnswer(index)"
                            class="bg-blue-500 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-600 transition"
                        >
                            Submit Answer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>




// Q&A Section state
const questions = ref([]);
const newQuestion = ref("");

// Add a new question
const addQuestion = () => {
    if (newQuestion.value.trim() === "") return;
    questions.value.push({
        question: newQuestion.value.trim(),
        answers: [],
        newAnswer: "",
        showReplyField: false,
    });
    newQuestion.value = "";
};

// Toggle reply field visibility
const toggleReplyField = (index) => {
    questions.value[index].showReplyField =
        !questions.value[index].showReplyField;
};

// Add an answer to a question
const addAnswer = (index) => {
    const qa = questions.value[index];
    if (qa.newAnswer.trim() === "") return;
    qa.answers.push(qa.newAnswer.trim());
    qa.newAnswer = "";
    qa.showReplyField = false;
};