<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center max-w-lg">
            <!-- Image -->
            <img
                src="/images/Not-found.png"
                alt="Page not found"
                class="w-2/3 mx-auto mb-6 sm:w-1/2"
            />

            <!-- Error Message -->
            <h1 class="text-5xl font-extrabold text-gray-800 mb-4">404</h1>
            <p class="text-lg text-gray-600 mb-6">
                Oops! The page you're looking for doesn't exist or has been
                moved.
            </p>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
                <router-link
                    to="/"
                    class="px-6 py-3 bg-lime-600 text-white font-medium text-lg rounded-lg hover:bg-lime-700 transition duration-300"
                >
                    Go to Homepage
                </router-link>
                <router-link
                    to="/contact"
                    class="px-6 py-3 bg-gray-200 text-gray-800 font-medium text-lg rounded-lg hover:bg-gray-300 transition duration-300"
                >
                    Contact Us
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
// This is a static 404 page component
// Add your routes in `router-link` to connect to the actual app routes.
</script>

<style scoped>
/* Customize further if needed */
</style>
