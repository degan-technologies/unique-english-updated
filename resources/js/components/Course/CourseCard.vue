<script setup>
import { ref, onMounted } from "vue";
import { useCartStore } from "@/store/cart";

// Typing animation logic
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

// Cart store and function
const cartStore = useCartStore();
const addToCart = (course) => {
    cartStore.addToCart(course); // Call the store's method to add the course
};

// Course data
const courses = ref([
    {
        id: 1,
        image: "/images/course-1.jpg",
        title: "Vue.js 3 - The Complete Guide",
        author: "John Doe",
        rating: 4.5,
        price: 29.99,
        updatedAt: "2025-01-01",
        totalHours: 12,
        fullDescription: "Learn Vue.js from scratch in this complete guide. Understand the core concepts and advanced features to build robust web applications.",
    },
    {
        id: 2,
        image: "/images/course-2.jpg",
        title: "Mastering JavaScript",
        author: "Jane Smith",
        rating: 4.8,
        price: 19.99,
        updatedAt: "2025-01-15",
        totalHours: 8,
        fullDescription: "This course covers advanced JavaScript techniques and frameworks to make you an expert in front-end development.",
    },
    {
        id: 3,
        image: "/images/course-3.webp",
        title: "Tailwind CSS for Beginners",
        author: "Alex Johnson",
        rating: 4.7,
        price: 24.99,
        updatedAt: "2025-01-10",
        totalHours: 10,
        fullDescription: "Master Tailwind CSS in this beginner-friendly course. Learn how to build beautiful, responsive designs quickly.",
    },
    {
        id: 4,
        image: "/images/course-4.jpeg",
        title: "React.js Crash Course",
        author: "Chris Brown",
        rating: 4.6,
        price: 34.99,
        updatedAt: "2025-01-18",
        totalHours: 15,
        fullDescription: "Get up to speed with React.js in this fast-paced crash course. Learn hooks, state management, and component design.",
    },
]);
</script>


<template>
<div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen relative">
    <!-- Popular Courses Title -->
    <h1 class="text-2xl font-bold mb-6 text-center">Popular Courses</h1>
    <!-- Typing Animation -->
    <div class="text-center mb-4">
        <h1 class="typing-text text-xl sm:text-2xl font-semibold text-blue-600">
            {{ displayedText }}
            <span class="cursor">|</span>
        </h1>
    </div>
    <!-- Responsive grid layout -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative">
        <div v-for="course in courses" :key="course.id" class="relative p-4 bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 hover:scale-105 cursor-pointer group">
            <!-- Image with Play Button -->
            <div class="relative group">
                <img :src="course.image" :alt="course.title" class="w-full h-40 object-cover rounded-t-lg" />
                <!-- Play Button Overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-t-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button class="bg-white rounded-full p-3 shadow-md text-blue-500 hover:text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25l13.5 6.75-13.5 6.75V5.25z" />
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
                    <span class="ml-1 text-sm text-gray-600">{{ course.rating }}</span>
                </div>
                <div class="mt-6 text-lg font-semibold text-blue-500 flex items-center">
                    ${{ course.price.toFixed(2) }}

                    <!-- Add to Cart Button -->
                    <button @click="addToCart(course)" class="ml-auto focus:outline-none">
                        <span class="mb-2 material-icons absolute bottom-20 right-8 bg-lime-500 text-white rounded-full bg-blue-500 p-3 hover:bg-lime-700 ">
                            shopping_cart
                        </span>

                    </button>

                </div>

                <!-- Get Started Button -->
                 
                <button class="mt-4 border border-lime-700 text-lime-800 px-4 py-2 rounded hover:bg-lime-800 hover:text-white focus:outline-none transition-colors font-medium text-lg" @click="getStarted(course)">
                    Get Started
                </button>
            </div>
        </div>
    </div>
</div>
</template>
