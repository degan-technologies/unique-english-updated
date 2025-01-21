<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen">
        <!-- Popular Courses Title -->
        <h1 class="text-2xl font-bold mb-6 text-center">Popular Courses</h1>
        <!-- Typing Animation -->
        <div class="text-center mb-4">
            <h1
                class="typing-text text-xl sm:text-2xl font-semibold text-blue-600"
            >
                {{ displayedText }}
                <span class="cursor">|</span>
            </h1>
        </div>
        <!-- Responsive grid layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                v-for="course in courses"
                :key="course.id"
                class="relative p-4 bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 hover:scale-105 cursor-pointer"
            >
                <!-- Image with Play Button -->
                <div class="relative group">
                    <img
                        :src="course.image"
                        :alt="course.title"
                        class="w-full h-40 object-cover rounded-t-lg"
                    />
                    <!-- Play Button Overlay -->
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-t-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    >
                        <button
                            class="bg-white rounded-full p-3 shadow-md text-blue-500 hover:text-blue-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5.25 5.25l13.5 6.75-13.5 6.75V5.25z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="p-4 flex flex-col">
                    <h2 class="text-lg font-bold mb-2">{{ course.title }}</h2>
                    <p class="text-sm text-gray-600">By: {{ course.author }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-yellow-500">&#9733;</span>
                        <span class="ml-1 text-sm text-gray-600">{{
                            course.rating
                        }}</span>
                    </div>
                    <div
                        class="mt-4 text-lg font-semibold text-blue-500 text-right"
                    >
                        ${{ course.price.toFixed(2) }}
                    </div>
                    <!-- Buy Now Button -->
                    <button
                        class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 focus:outline-none transition-colors"
                    >
                        Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Typing animation
const words = ["Vocabulary", "Grammar", "Idioms", "Phrases"];
const displayedText = ref("");
const typingSpeed = 150; // Speed of typing in ms
const erasingSpeed = 100; // Speed of erasing in ms
const delayBetweenWords = 2000; // Delay before erasing starts in ms
let wordIndex = 0;
let charIndex = 0;
let isErasing = false;

const type = () => {
    if (!isErasing) {
        if (charIndex < words[wordIndex].length) {
            displayedText.value += words[wordIndex][charIndex];
            charIndex++;
            setTimeout(type, typingSpeed);
        } else {
            setTimeout(() => {
                isErasing = true;
                type();
            }, delayBetweenWords);
        }
    } else {
        if (charIndex > 0) {
            displayedText.value = displayedText.value.slice(0, -1);
            charIndex--;
            setTimeout(type, erasingSpeed);
        } else {
            isErasing = false;
            wordIndex = (wordIndex + 1) % words.length;
            setTimeout(type, typingSpeed);
        }
    }
};

onMounted(() => {
    type();
});

// Course data
const courses = [
    {
        id: 1,
        image: "/images/course-1.jpg",
        title: "Vue.js 3 - The Complete Guide",
        author: "John Doe",
        rating: 4.5,
        price: 29.99,
    },
    {
        id: 2,
        image: "/images/course-2.jpg",
        title: "Mastering JavaScript",
        author: "Jane Smith",
        rating: 4.8,
        price: 19.99,
    },
    {
        id: 3,
        image: "/images/course-3.webp",
        title: "Tailwind CSS for Beginners",
        author: "Alex Johnson",
        rating: 4.7,
        price: 24.99,
    },
    {
        id: 4,
        image: "/images/course-4.jpeg",
        title: "React.js Crash Course",
        author: "Chris Brown",
        rating: 4.6,
        price: 34.99,
    },
];
</script>

<style scoped>
/* Typing text styles */
.typing-text {
    display: inline-flex;
    align-items: center;
}

.cursor {
    display: inline-block;
    width: 2px;
    background-color: black;
    animation: blink 0.7s infinite;
}

/* Cursor blinking animation */
@keyframes blink {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
}
</style>
