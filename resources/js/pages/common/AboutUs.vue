<script setup>
import { ref } from "vue";

// State for expandable text
const isExpanded = ref(false);

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};
</script>

<template>
    <section class="bg-gray-50 py-12 px-4" id="about">
        <div
            class="container mx-auto flex flex-col lg:flex-row items-center gap-8"
        >
            <!-- Image Section -->
            <div class="lg:w-1/3 hidden lg:block">
                <img
                    src="/images/mehari.jpg"
                    alt="About Us"
                    class="rounded-lg shadow-md w-full"
                />
            </div>

            <!-- Text Content -->
            <div class="lg:w-2/3 text-center lg:text-left">
                <h2 class="text-3xl font-bold text-lime-700 mb-4">About Us</h2>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Welcome to our platform! We specialize in offering engaging
                    and effective English learning experiences tailored to all
                    levels—from beginners to advanced learners.
                </p>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Whether you’re preparing for exams, improving your
                    communication skills, or expanding your vocabulary, our
                    mission is to help you achieve your goals with confidence
                    and ease.
                </p>

                <!-- Expandable Text -->
                <p
                    v-if="isExpanded"
                    class="text-gray-600 text-base leading-relaxed mb-4"
                >
                    Our platform offers interactive lessons, personalized
                    quizzes, and live sessions with certified instructors. We
                    emphasize practical learning by incorporating real-life
                    scenarios, idioms, and phrases commonly used in everyday
                    conversations. Additionally, you can track your progress,
                    set learning goals, and connect with a vibrant community of
                    language enthusiasts.
                </p>

                <!-- See More Button -->
                <button
                    v-if="!isExpanded"
                    @click="toggleExpanded"
                    class="text-lime-700 text-sm font-semibold hover:underline block mb-6"
                >
                    See More...
                </button>
                <button
                    v-if="isExpanded"
                    @click="toggleExpanded"
                    class="text-lime-700 text-sm font-semibold hover:underline block mb-6"
                >
                    See Less
                </button>

                <!-- Call to Action Button -->
                <!-- <button
                    class="bg-lime-700 text-white px-5 py-2 rounded-md text-sm shadow hover:bg-lime-800 transition duration-300"
                >
                    Learn More
                </button> -->
            </div>
        </div>
    </section>
</template>
