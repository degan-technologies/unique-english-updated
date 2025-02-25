<template>
    <section
        class="bg-cover bg-center bg-no-repeat py-16"
        :style="{ backgroundImage: `url('/images/here-back.jpg')` }"
    >
        <div
            class="container mx-auto flex flex-col-reverse lg:flex-row items-center px-6"
        >
            <!-- Content Section -->
            <div class="text-center lg:text-left lg:max-w-lg">
                <h1
                    class="text-4xl md:text-5xl font-bold leading-tight text-gray-800"
                >
                    Learn Without Limits, Anytime, Anywhere
                </h1>
                <p class="mt-4 text-gray-600 text-lg">
                    Empower your future with world-class courses, expert
                    instructors, and a flexible learning experience tailored to
                    your needs.
                </p>
                <div
                    class="mt-6 flex justify-center lg:justify-start space-x-4"
                >
                    <button
                        @click="getStarted"
                        class="bg-lime-700 text-white px-6 py-3 rounded-md hover:bg-lime-800 transition"
                    >
                        Get Started for Free
                    </button>
                    <button
                        @click="exploreCourses"
                        class="bg-gray-800 text-white px-6 py-3 rounded-md hover:bg-gray-900 transition"
                    >
                        Explore Courses
                    </button>
                </div>
            </div>

            <!-- Image Section -->
            <div
                class="flex justify-center lg:justify-end w-full lg:w-1/2 mt-8 lg:mt-0"
            >
                <img
                    src="/images/Learning.png"
                    alt="E-learning Illustration"
                    class="rounded-lg shadow-lg max-w-full"
                />
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
    name: "HeroSection",
    setup() {
        const getStarted = () => {
            alert("Redirecting to sign-up page...");
        };

        const exploreCourses = () => {
            alert("Redirecting to courses page...");
        };

        return {
            getStarted,
            exploreCourses,
        };
    },
});
</script>

<style scoped>
/* Optional: Custom styles can be added here if required */
</style>
