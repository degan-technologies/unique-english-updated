<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen">
        <!-- Popular Books Title -->
        <h1 class="text-2xl font-bold mb-6 text-center text-lime-700">
            Popular Books
        </h1>

        <!-- Responsive grid layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                v-for="book in books"
                :key="book.id"
                class="relative p-4 bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 hover:scale-105 cursor-pointer flex flex-col"
            >
                <!-- Image with Play Button -->
                <div class="relative group">
                    <img
                        :src="book.image"
                        :alt="book.title"
                        class="w-full h-40 object-cover rounded-t-lg"
                    />
                </div>

                <!-- Book Info -->
                <div class="flex-1 p-4">
                    <h2 class="text-lg font-bold mb-2 text-lime-700">
                        {{ book.title }}
                    </h2>
                    <p class="text-sm text-gray-600">By: {{ book.author }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-yellow-500">&#9733;</span>
                        <span class="ml-1 text-sm text-gray-600">{{
                            book.rating
                        }}</span>
                    </div>
                    <div
                        class="mt-4 text-lg font-semibold text-lime-700 text-right"
                    >
                        ${{ book.price.toFixed(2) }}
                    </div>
                </div>

                <!-- Buy Now Button -->
                <div class="p-4 pt-0 mt-auto flex justify-center">
                    <button
                        class="bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 focus:outline-none transition-colors w-full"
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
const words = ["Fiction", "Science", "Fantasy", "Mystery"];
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

// Book data
const books = [
    {
        id: 1,
        image: "/images/book-1.webp",
        title: "The Great Gatsby",
        author: "F. Scott Fitzgerald",
        rating: 4.7,
        price: 15.99,
    },
    {
        id: 2,
        image: "/images/book-2.jpeg",
        title: "1984",
        author: "George Orwell",
        rating: 4.8,
        price: 18.99,
    },
    {
        id: 3,
        image: "/images/book-3.jpeg",
        title: "To Kill a Mockingbird",
        author: "Harper Lee",
        rating: 4.9,
        price: 14.99,
    },
    {
        id: 4,
        image: "/images/book-4.jpg",
        title: "The Catcher in the Rye",
        author: "J.D. Salinger",
        rating: 4.6,
        price: 17.99,
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
    background-color: limegreen;
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
