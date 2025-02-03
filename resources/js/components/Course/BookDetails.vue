<template>
    <div class="p-6 bg-gray-100 min-h-screen flex justify-center">
        <div
            class="max-w-4xl w-full bg-white rounded-lg shadow-lg p-6 flex flex-col md:flex-row gap-6"
        >
            <!-- Left Side: Book Details -->
            <div class="flex-1">
                <!-- Book Image -->
                <img
                    :src="book.image"
                    :alt="book.title"
                    class="w-full h-60 object-cover rounded-lg mb-4"
                />

                <!-- Book Title -->
                <h2 class="text-2xl font-bold text-lime-700">
                    {{ book.title }}
                </h2>

                <!-- Course Overview -->
                <div class="mt-4">
                    <h3 class="text-lg font-bold text-lime-700">Overview</h3>
                    <p class="text-gray-700">
                        {{
                            isExpanded
                                ? book.overview
                                : shortText(book.overview)
                        }}
                        <button
                            @click="isExpanded = !isExpanded"
                            class="text-lime-700 font-semibold underline ml-2"
                        >
                            {{ isExpanded ? "See Less" : "See More" }}
                        </button>
                    </p>
                </div>

                <!-- Course Content -->
                <div class="mt-4">
                    <h3 class="text-lg font-bold text-lime-700">
                        Course Content
                    </h3>
                    <p class="text-gray-700">{{ book.courseContent }}</p>
                </div>

                <!-- Course Description -->
                <div class="mt-4">
                    <h3 class="text-lg font-bold text-lime-700">Description</h3>
                    <p class="text-gray-700">
                        {{
                            isExpandedDesc
                                ? book.description
                                : shortText(book.description)
                        }}
                        <button
                            @click="isExpandedDesc = !isExpandedDesc"
                            class="text-lime-700 font-semibold underline ml-2"
                        >
                            {{ isExpandedDesc ? "See Less" : "See More" }}
                        </button>
                    </p>
                </div>
            </div>

            <!-- Right Side: Course Pricing and Details -->
            <div class="w-full md:w-1/3 bg-gray-50 rounded-lg p-6 shadow-md">
                <button
                    class="mt-6 bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 w-full"
                >
                    Buy Now
                </button>

                <p class="text-gray-600 text-sm mt-4">
                    Last Updated: {{ book.lastUpdated }}
                </p>

                <div class="mt-4">
                    <p class="text-lg font-semibold text-red-600">
                        Discount Price: {{ book.discountPrice }} Birr
                    </p>
                    <p class="text-gray-500 line-through">
                        Original Price: {{ book.originalPrice }} Birr
                    </p>
                </div>

                <div class="mt-4 space-y-2">
                    <p><strong>Chapters:</strong> {{ book.chapters }}</p>
                    <p><strong>Quizzes:</strong> {{ book.quizzes }}</p>
                    <p><strong>Duration:</strong> {{ book.duration }} Hours</p>
                </div>

                <!-- Countdown Timer -->
                <div
                    v-if="timeLeft.days >= 0"
                    class="mt-4 p-4 bg-red-100 text-red-600 text-center rounded-lg"
                >
                    <p class="font-semibold">This offer ends in:</p>
                    <p class="text-xl font-bold">
                        {{ timeLeft.days }} days {{ timeLeft.hours }}h :
                        {{ timeLeft.minutes }}m : {{ timeLeft.seconds }}s
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

// Static book details
const book = ref({
    image: "/images/book-4.jpg", // Change to your book image
    title: "Conversational English - Amharic",
    lastUpdated: "November 29, 2024",
    originalPrice: 600,
    discountPrice: 500,
    chapters: 1,
    quizzes: 0,
    duration: 100,
    overview:
        "This is a book about idioms and phrases. Learning natural English is essential to communicate with English speakers. In this book, 3,000 idioms and phrases have been discussed with translations and examples.",
    courseContent: "Conversational idioms and phrases, Idioms and phrases.",
    description:
        "This course is designed to improve conversational skills by learning 3,000 commonly used idioms and phrases in English and Amharic. Learners will understand their meanings, cultural nuances, and real-life applications.",
});

// Expand/Collapse States
const isExpanded = ref(false);
const isExpandedDesc = ref(false);

// Shorten text for "See More" feature
const shortText = (text) =>
    text.length > 100 ? text.substring(0, 100) + "..." : text;

// Countdown Timer for Discount
const timeLeft = ref({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
});

// Set the discount expiration date (modify as needed)
const discountEndDate = new Date();
discountEndDate.setDate(discountEndDate.getDate() + 3); // Expires in 3 days

const updateCountdown = () => {
    const now = new Date().getTime();
    const distance = discountEndDate - now;

    if (distance > 0) {
        timeLeft.value.days = Math.floor(distance / (1000 * 60 * 60 * 24));
        timeLeft.value.hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        timeLeft.value.minutes = Math.floor(
            (distance % (1000 * 60 * 60)) / (1000 * 60)
        );
        timeLeft.value.seconds = Math.floor((distance % (1000 * 60)) / 1000);
    } else {
        timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }
};

// Start the countdown timer
onMounted(() => {
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
