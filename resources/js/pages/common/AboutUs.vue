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
            <div class="lg:w-1/3 lg:block text-center">
                <h2 class="text-3xl lg:hidden font-bold text-orange-500 mb-4">
                    About Us
                </h2>
                <img
                    src="/images/maraki-eng.webp"
                    alt="About Us"
                    class="rounded-lg w-full h-80 object-cover"
                />
            </div>

            <!-- Text Content -->
            <div class="lg:w-2/3 text-center lg:text-left">
                <h2
                    class="text-3xl hidden lg:flex font-bold text-orange-500 mb-4"
                >
                    About Us
                </h2>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Unlock Your Potential with Our English Learning Platform!
                    Whether you're just starting out or aiming to refine your
                    fluency, we’re here to guide you every step of the way.
                </p>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Our platform is designed to support learners at every
                    level—offering structured lessons, engaging practice
                    activities, and live sessions led by professional English
                    tutors. From grammar and vocabulary to conversation skills
                    and exam prep, we cover it all.
                </p>

                <!-- Expandable Text -->
                <p
                    v-if="isExpanded"
                    class="text-gray-600 text-base leading-relaxed mb-4"
                >
                    You’ll enjoy interactive content that mirrors real-world
                    situations, helping you speak and understand English with
                    confidence. Track your improvement, set personal milestones,
                    and join a global community of motivated learners just like
                    you. Let’s make learning English an exciting and rewarding
                    journey—together!
                </p>

                <!-- See More Button -->
                <button
                    v-if="!isExpanded"
                    @click="toggleExpanded"
                    class="text-orange-500 text-sm font-semibold hover:underline block mb-6"
                >
                    See More...
                </button>
                <button
                    v-if="isExpanded"
                    @click="toggleExpanded"
                    class="text-orange-500 text-sm font-semibold hover:underline block mb-6"
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
