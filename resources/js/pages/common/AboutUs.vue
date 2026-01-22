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
            <div class="lg:w-1/3 lg:block">
                <img
                    src="/images/mehari.JPG"
                    alt="About Us"
                    class="rounded-lg shadow-md w-full"
                />
            </div>

            <!-- Text Content -->
            <div class="lg:w-2/3 text-center lg:text-left">
                <h2 class="text-3xl font-bold text-lime-700 mb-4">About Us</h2>
                <p
                    class="text-gray-600 text-base leading-relaxed mb-4 text-justify"
                >
                    Welcome to our platform! We specialize in offering engaging
                    and effective English learning experiences tailored to all
                    levels from beginners to advanced learners.
                </p>
                <p
                    class="text-gray-600 text-base leading-relaxed mb-4 text-justify"
                >
                    Whether you're preparing for exams, improving your
                    communication skills, or expanding your vocabulary, our
                    mission is to help you achieve your goals with confidence
                    and ease.
                </p>

                <!-- Expandable Text -->
                <p
                    v-if="isExpanded"
                    class="text-gray-600 text-base leading-relaxed mb-4 text-justify"
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
                    class="text-lime-700 text-sm font-semibold hover:underline hover:text-lime-800 transition-colors duration-200 block mb-6"
                >
                    See More...
                </button>
                <button
                    v-if="isExpanded"
                    @click="toggleExpanded"
                    class="text-lime-700 text-sm font-semibold hover:underline hover:text-lime-800 transition-colors duration-200 block mb-6"
                >
                    See Less
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Ensure proper text justification */
p {
    text-align: justify;
    hyphens: auto;
    word-spacing: normal;
    letter-spacing: normal;
}

/* Better line height for readability */
.leading-relaxed {
    line-height: 1.75;
}

/* Smooth transitions for interactive elements */
button {
    transition: all 0.2s ease-in-out;
}

button:hover {
    transform: translateY(-1px);
}
</style>
