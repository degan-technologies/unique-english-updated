<template>
    <div
        class="course-details-container p-6 max-w-screen-xl mx-auto bg-white rounded-lg shadow-lg h-screen overflow-x-hidden overflow-y-auto"
    >
        <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
            <!-- Left Card: Course Content -->
            <div class="bg-white rounded-lg shadow-lg pb-12">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold">
                        The Art of Filmmaking: Mastering Storytelling and
                        Editing with Adobe Premiere Pro
                    </h1>
                    <p class="text-lg text-gray-700 mt-2">
                        A course by <strong>Robel Birhanu</strong>
                    </p>
                </div>
                <div class="overflow-hidden max-w-full h-auto rounded-lg my-6">
                    <img
                        src="/images/course-1.jpg"
                        alt="Course Image"
                        class="w-full h-auto rounded-lg object-cover transform hover:scale-105 transition-transform duration-300 shadow-lg hover:shadow-xl"
                    />
                </div>
                <div class="text-center mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">
                            What You Will Learn
                        </h2>
                        <!-- Global Progress Circle -->
                        <div class="relative">
                            <div
                                class="w-16 h-16 rounded-full flex items-center justify-center"
                                role="progressbar"
                                :aria-valuenow="calculateGlobalProgress()"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                :style="{
                                    background: `conic-gradient(
                                        green ${calculateGlobalProgress()}%, 
                                        red ${calculateGlobalProgress()}% 100%
                                    )`,
                                }"
                            >
                                <span class="text-lg font-bold text-white">
                                    {{ calculateGlobalProgress() }}%
                                </span>
                            </div>
                            <p class="text-center text-sm mt-2 text-gray-700">
                                Progress:
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
                                        {
                                            'rotate-180': module.showLessons,
                                        },
                                    ]"
                                    class="transition-transform duration-300"
                                >
                                    &#x25BC;
                                </i>
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

                                <div v-if="!quizSubmitted[moduleIndex]">
                                    <div class="mb-6">
                                        <p class="text-lg font-semibold mb-4">
                                            Question
                                            {{
                                                currentQuestion[moduleIndex] + 1
                                            }}
                                            of
                                            {{ module.quiz.length }}
                                        </p>
                                        <p class="text-gray-500">
                                            {{
                                                module.quiz[
                                                    currentQuestion[moduleIndex]
                                                ].text
                                            }}
                                        </p>
                                        <div class="flex flex-col gap-2 mt-2">
                                            <label
                                                v-for="(
                                                    option, optIdx
                                                ) in module.quiz[
                                                    currentQuestion[moduleIndex]
                                                ].options"
                                                :key="optIdx"
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    type="radio"
                                                    :name="
                                                        moduleIndex +
                                                        '-question-' +
                                                        currentQuestion[
                                                            moduleIndex
                                                        ]
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
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <button
                                            v-if="
                                                currentQuestion[moduleIndex] > 0
                                            "
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
                                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors ml-auto"
                                        >
                                            Next
                                        </button>
                                        <button
                                            v-else
                                            @click="submitQuiz(moduleIndex)"
                                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors ml-auto"
                                        >
                                            Submit
                                        </button>
                                    </div>
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
                    <a href="#" class="text-blue-500 hover:underline">
                        <i class="fas fa-certificate text-teal-500 mr-2"></i>
                        <strong>Certificate of completion</strong>
                    </a>
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
                selected: "",
            },
            {
                text: "Who invented the first motion picture?",
                options: ["Thomas Edison", "George Eastman", "Louis Le Prince"],
                correct: "Louis Le Prince",
                selected: "",
            },
        ],
    },
    {
        title: "Module 2: Camera Techniques",
        showLessons: false,
        lessons: [
            "Lesson 1: Camera Angles",
            "Lesson 2: Camera Movements",
            "Lesson 3: Lighting Techniques",
        ],
        completedLessons: [],
        quizStarted: false,
        quiz: [
            {
                text: "What is the purpose of a close-up shot?",
                options: [
                    "To show emotion",
                    "To show context",
                    "To show action",
                ],
                correct: "To show emotion",
                selected: "",
            },
            {
                text: "What is the term for the steady movement of the camera?",
                options: ["Tilt", "Dolly", "Zoom"],
                correct: "Dolly",
                selected: "",
            },
        ],
    },
    {
        title: "Module 3: Audio and Sound Design",
        showLessons: false,
        lessons: [
            "Lesson 1: Importance of Sound",
            "Lesson 2: Microphone Types",
            "Lesson 3: Sound Editing Techniques",
        ],
        completedLessons: [],
        quizStarted: false,
        quiz: [
            {
                text: "Which microphone is best for recording interviews?",
                options: ["Lavalier", "Shotgun", "Condenser"],
                correct: "Lavalier",
                selected: "",
            },
            {
                text: "What is Foley sound?",
                options: [
                    "Sound created in post-production",
                    "Live recorded sound on set",
                    "Digital sound effects",
                ],
                correct: "Sound created in post-production",
                selected: "",
            },
        ],
    },
    {
        title: "Module 4: Video Editing with Adobe Premiere Pro",
        showLessons: false,
        lessons: [
            "Lesson 1: Introduction to Adobe Premiere Pro",
            "Lesson 2: Basic Editing Tools",
            "Lesson 3: Advanced Editing Techniques",
        ],
        completedLessons: [],
        quizStarted: false,
        quiz: [
            {
                text: "What does the 'cut' tool do in Premiere Pro?",
                options: ["Splits clips", "Applies effects", "Renders video"],
                correct: "Splits clips",
                selected: "",
            },
            {
                text: "Which format is best for exporting videos for YouTube?",
                options: ["MP4", "MOV", "AVI"],
                correct: "MP4",
                selected: "",
            },
        ],
    },
]);

// Track current question index for each module
const currentQuestion = ref(Array(modules.value.length).fill(0));

// Track quiz submission status for each module
const quizSubmitted = ref(Array(modules.value.length).fill(false));

// Toggle lessons visibility for a specific module
const toggleLesson = (moduleIndex) => {
    modules.value[moduleIndex].showLessons =
        !modules.value[moduleIndex].showLessons;
};

// Toggle lesson completion for a specific module
const toggleLessonCompletion = (moduleIndex, lessonIndex) => {
    const completedLessons = modules.value[moduleIndex].completedLessons;
    const lesson = lessonIndex;
    if (completedLessons.includes(lesson)) {
        completedLessons.splice(completedLessons.indexOf(lesson), 1);
    } else {
        completedLessons.push(lesson);
    }
};

// Start quiz for a specific module
const startQuiz = (moduleIndex) => {
    modules.value[moduleIndex].quizStarted = true;
    currentQuestion.value[moduleIndex] = 0; // Reset question index
};

// Next question for a specific module
const nextQuestion = (moduleIndex) => {
    if (
        currentQuestion.value[moduleIndex] <
        modules.value[moduleIndex].quiz.length - 1
    ) {
        currentQuestion.value[moduleIndex]++;
    }
};

// Previous question for a specific module
const prevQuestion = (moduleIndex) => {
    if (currentQuestion.value[moduleIndex] > 0) {
        currentQuestion.value[moduleIndex]--;
    }
};

// Submit quiz for a specific module
const submitQuiz = (moduleIndex) => {
    quizSubmitted.value[moduleIndex] = true;
};

// Calculate score for a specific module
const calculateScore = (moduleIndex) => {
    const quiz = modules.value[moduleIndex].quiz;
    const correctAnswers = quiz.filter(
        (question) => question.selected === question.correct
    ).length;
    return Math.round((correctAnswers / quiz.length) * 100);
};

// Retake quiz for a specific module
const retakeQuiz = (moduleIndex) => {
    modules.value[moduleIndex].quiz.forEach((question) => {
        question.selected = "";
    });
    quizSubmitted.value[moduleIndex] = false;
    currentQuestion.value[moduleIndex] = 0;
    modules.value[moduleIndex].quizStarted = false;
};

// Calculate global progress percentage
const calculateGlobalProgress = () => {
    let totalLessons = 0;
    let completedLessons = 0;
    modules.value.forEach((module) => {
        totalLessons += module.lessons.length;
        completedLessons += module.completedLessons.length;
    });
    return Math.round((completedLessons / totalLessons) * 100);
};
</script>

<style scoped>
.course-details-container {
    max-height: calc(100vh - 48px);
}

.bg-green-500:hover,
.bg-blue-500:hover,
.bg-gray-500:hover {
    transition: background-color 0.3s ease;
}

.bg-green-500 {
    background-color: #38a169;
}

.bg-blue-500 {
    background-color: #3182ce;
}

.bg-gray-500 {
    background-color: #6b7280;
}
</style>
