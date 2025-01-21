<template>
    <div
        class="course-details-container p-6 max-w-screen-xl mx-auto bg-white rounded-lg shadow-lg"
    >
        <!-- Flex container to align two cards side by side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
            <!-- Left Card: Course Content (Increased Width, Scrollable at Page Level) -->
            <div class="course-content-card bg-white rounded-lg shadow-lg">
                <!-- Title and Author -->
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold">
                        The Art of Filmmaking: Mastering Storytelling and
                        Editing with Adobe Premiere Pro
                    </h1>
                    <p class="text-lg text-gray-700 mt-2">
                        A course by <strong>Robel Birhanu</strong>
                    </p>

                    <!-- Image with new styles -->
                    <div class="image-container my-6">
                        <img
                            src="/images/course-1.jpg"
                            alt="Course Image"
                            class="course-image rounded-lg shadow-lg transform hover:scale-105 transition-all duration-300"
                        />
                    </div>
                </div>

                <!-- About the Course Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">About the Course</h2>
                    <p class="text-gray-700 mt-4">
                        Welcome to my course! I’m Robel Birhanu, and in this
                        course, we’ll explore the fundamentals of filmmaking,
                        including story development, pre-production, directing,
                        camera techniques, and editing with Adobe Premiere Pro.
                    </p>
                </div>

                <!-- "What You Will Learn" Section -->
                <div>
                    <h2 class="text-xl font-semibold mb-4">
                        What You Will Learn
                    </h2>
                    <div
                        v-for="(module, index) in modules"
                        :key="index"
                        class="mb-6"
                    >
                        <div class="flex justify-between items-center">
                            <!-- Module as a button -->
                            <button
                                @click="toggleLesson(index)"
                                class="text-blue-500 hover:text-blue-700 text-xl w-full text-left py-4 px-4 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition duration-300"
                            >
                                <span class="font-semibold">{{
                                    module.title
                                }}</span>
                                <i
                                    :class="[
                                        {
                                            'transform rotate-180':
                                                module.showLessons,
                                        },
                                        'ml-2',
                                    ]"
                                >
                                    &#x25BC;
                                </i>
                            </button>
                        </div>

                        <!-- Show lessons when toggled -->
                        <div v-if="module.showLessons" class="ml-4 mt-2">
                            <ul>
                                <li
                                    v-for="(lesson, idx) in module.lessons"
                                    :key="idx"
                                    class="text-gray-700 flex items-center"
                                >
                                    <span class="mr-2">▶️</span>
                                    {{ lesson }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Course Details (Decreased Width, Positioned at the End) -->
            <div
                class="course-details-card bg-gray-100 p-6 rounded-lg shadow-lg"
            >
                <button
                    class="bg-green-500 text-white px-6 py-2 rounded-full mb-4 hover:bg-green-600 transition-colors w-full"
                >
                    Enroll Now
                </button>
                <h2 class="text-xl font-semibold mb-4">Course Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i
                        ><strong>Level:</strong> Beginners
                    </div>
                    <div>
                        <i class="fas fa-language text-green-500 mr-2"></i
                        ><strong>Language:</strong> Amharic
                    </div>
                    <div>
                        <i class="fas fa-clock text-yellow-500 mr-2"></i
                        ><strong>Duration:</strong> 5:02
                    </div>
                    <div>
                        <i class="fas fa-tasks text-red-500 mr-2"></i
                        ><strong>Activities:</strong> 30
                    </div>
                    <div>
                        <i class="fas fa-tv text-purple-500 mr-2"></i>
                        <strong>Access on:</strong> Mobile, Desktop, and TV
                    </div>
                    <div>
                        <i class="fas fa-users text-indigo-500 mr-2"></i
                        ><strong>Lifetime access to the community</strong>
                    </div>
                    <div>
                        <i class="fas fa-certificate text-teal-500 mr-2"></i
                        ><strong>Certificate of completion</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Demo data for modules with lessons
const modules = ref([
    {
        title: "Module 1: Introduction to Filmmaking",
        showLessons: false,
        lessons: [
            "Lesson 1: What is Filmmaking?",
            "Lesson 2: History of Filmmaking",
            "Lesson 3: Filmmaking Techniques",
        ],
    },
    {
        title: "Module 2: Pre-production and Planning",
        showLessons: false,
        lessons: [
            "Lesson 1: Scriptwriting",
            "Lesson 2: Storyboarding",
            "Lesson 3: Budgeting and Planning",
        ],
    },
    {
        title: "Module 3: Directing and Working with Actors",
        showLessons: false,
        lessons: [
            "Lesson 1: Directing Techniques",
            "Lesson 2: Actor Communication",
            "Lesson 3: Casting",
        ],
    },
    {
        title: "Module 4: Camera Techniques and Cinematography",
        showLessons: false,
        lessons: [
            "Lesson 1: Camera Angles",
            "Lesson 2: Lighting for Filmmaking",
            "Lesson 3: Cinematography Techniques",
        ],
    },
    {
        title: "Module 5: Editing Fundamentals with Adobe Premiere Pro",
        showLessons: false,
        lessons: [
            "Lesson 1: Introduction to Premiere Pro",
            "Lesson 2: Basic Editing Tools",
            "Lesson 3: Color Correction",
        ],
    },
    {
        title: "Module 6: Color Grading, Exporting, and Distributing Your Video",
        showLessons: false,
        lessons: [
            "Lesson 1: Introduction to Color Grading",
            "Lesson 2: Exporting Your Video",
            "Lesson 3: Distributing Your Content",
        ],
    },
]);

// Toggle lessons visibility
const toggleLesson = (index) => {
    modules.value[index].showLessons = !modules.value[index].showLessons;
};
</script>

<style scoped>
/* Custom Styles */
.course-details-container {
    max-width: 100%;
    margin: 0 auto;
    overflow-x: hidden; /* Hide horizontal scrollbar */
    overflow-y: auto; /* Ensure page content scrolls vertically */
    height: 100vh; /* Ensure full height */
}

button {
    cursor: pointer;
    text-decoration: none; /* Remove underline from buttons */
}

button:hover {
    text-decoration: none; /* Remove underline on hover */
}

ul {
    list-style-type: none;
    padding-left: 0;
}

li {
    margin: 5px 0;
}

.course-content-card {
    background-color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding-bottom: 50px;
}

.course-details-card {
    background-color: #f7fafc;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 320px; /* Right card width */
    position: sticky; /* Sticky on larger screens */
    top: 0;
    height: fit-content;
    padding: 1.5rem;
    margin-top: 0; /* Prevent unnecessary spacing */
}

.grid {
    display: grid;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    .md\:grid-cols-2 {
        grid-template-columns: 2fr 1fr; /* Left side bigger than right */
    }
}

@media (max-width: 1023px) {
    .course-details-card {
        position: relative; /* Allow right card to scroll on mobile */
        width: 100%; /* Full width on mobile */
    }
}

/* Dropdown icon rotation */
button i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

/* New CSS for Image Styling */
.image-container {
    overflow: hidden;
    max-width: 100%;
    height: auto;
    border-radius: 10px;
}

.course-image {
    width: 100%;
    height: auto;
    border-radius: 10px;
    object-fit: cover;
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
}

.course-image:hover {
    transform: scale(1.05); /* Slight zoom effect on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}
</style>
