<script>
    export default {
        name: "LiveSessionLanding",
        methods: {
            joinSession() {
                alert("Redirecting to the live session...");
            },
        },
    };
</script>

<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-gray-800"
    >
        <!-- Header Section -->
        <header class="text-center mb-8 md:mb-12 px-4">
            <h1 class="text-3xl md:text-5xl font-bold mb-4 text-lime-700">
                Join Our Live Video Session
            </h1>
            <p class="text-base md:text-xl text-gray-600">
                Experience real-time learning, discussions, and collaboration
                from the comfort of your home.
            </p>
        </header>

        <!-- Cards Section -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 md:px-8 lg:px-16 w-full"
        >
            <!-- Card 1 -->
            <div
                class="relative bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300"
            >
                <div class="group relative overflow-hidden">
                    <img
                        src="/images/English-3.jpeg"
                        alt="Basic English"
                        class="w-full h-56 md:h-64 object-cover transform group-hover:scale-105 transition-transform duration-300"
                    />
                    <!-- Always visible Live Badge -->
                    <div
                        class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1 bg-black text-white text-sm md:text-base font-semibold border-2 border-red-500 rounded-full animate-pulse"
                    >
                        <span
                            class="w-3 h-3 bg-red-600 border border-white rounded-full"
                        ></span>
                        <span>Live</span>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <h2 class="text-lg md:text-2xl font-semibold text-lime-700">
                        Class Type: Private
                    </h2>
                    <p class="text-base md:text-lg text-gray-600 mb-4">
                        Basic/Intermediate/Advanced English
                    </p>
                    <p class="text-lg font-bold text-lime-700 mb-4">
                        Price: 12,000 Birr/Month
                    </p>
                    <button
                        @click="joinSession"
                        class="w-full bg-lime-700 hover:bg-lime-800 text-white text-sm md:text-base font-medium px-4 py-2 rounded-lg shadow-lg transition-all duration-300"
                    >
                        Join Now
                    </button>
                </div>
            </div>

            <!-- Card 2 -->
            <div
                class="relative bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300"
            >
                <div class="group relative overflow-hidden">
                    <img
                        src="/images/English-4.webp"
                        alt="Intermediate English"
                        class="w-full h-56 md:h-64 object-cover transform group-hover:scale-105 transition-transform duration-300"
                    />
                    <!-- Always visible Live Badge -->
                    <div
                        class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1 bg-black text-white text-sm md:text-base font-semibold border-2 border-red-500 rounded-full animate-pulse"
                    >
                        <span
                            class="w-3 h-3 bg-red-600 border border-white rounded-full"
                        ></span>
                        <span>Live</span>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <h2 class="text-lg md:text-2xl font-semibold text-lime-700">
                        Class Type: Group
                    </h2>
                    <p class="text-base md:text-lg text-gray-600 mb-4">
                        Basic/Intermediate/Advanced English
                    </p>
                    <p class="text-lg font-bold text-lime-700 mb-4">
                        Price: 8,000 Birr/Month
                    </p>
                    <button
                        @click="joinSession"
                        class="w-full bg-lime-700 hover:bg-lime-800 text-white text-sm md:text-base font-medium px-4 py-2 rounded-lg shadow-lg transition-all duration-300"
                    >
                        Join Now
                    </button>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="relative bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300"
            >
                <div class="group relative overflow-hidden">
                    <img
                        src="/images/English-5.webp"
                        alt="Advanced English"
                        class="w-full h-56 md:h-64 object-cover transform group-hover:scale-105 transition-transform duration-300"
                    />
                    <!-- Always visible Live Badge -->
                    <div
                        class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1 bg-black text-white text-sm md:text-base font-semibold border-2 border-red-500 rounded-full animate-pulse"
                    >
                        <span
                            class="w-3 h-3 bg-red-600 border border-white rounded-full"
                        ></span>
                        <span>Live</span>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <h2 class="text-lg md:text-2xl font-semibold text-lime-700">
                        Class Type: All
                    </h2>
                    <p class="text-base md:text-lg text-gray-600 mb-4">
                        Basic/Intermediate/Advanced English
                    </p>
                    <p class="text-lg font-bold text-lime-700 mb-4">
                        Price: 15,000 Birr/Month
                    </p>
                    <button
                        @click="joinSession"
                        class="w-full bg-lime-700 hover:bg-lime-800 text-white text-sm md:text-base font-medium px-4 py-2 rounded-lg shadow-lg transition-all duration-300"
                    >
                        Join Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* No additional custom CSS required as Tailwind manages the animations and styles */
</style>
