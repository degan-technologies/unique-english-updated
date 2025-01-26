<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="flex flex-col items-center">
            <!-- Spinner -->
            <div class="relative w-16 h-16">
                <div
                    class="absolute inset-0 border-4 border-lime-500 border-t-transparent rounded-full animate-spin"
                ></div>
                <div
                    class="absolute inset-1 border-4 border-lime-300 border-t-transparent rounded-full animate-spin-slow"
                ></div>
            </div>

            <!-- Loading Text -->
            <p class="text-lg font-medium text-gray-700 mt-4 animate-pulse">
                Loading...
            </p>
        </div>
    </div>
</template>

<script setup>
/* You can add logic here if needed, e.g., for conditionally showing/hiding the spinner. */
</script>

<style scoped>
/* Custom animation for a slower spinning effect */
@keyframes spin-slow {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 2s linear infinite;
}
</style>
