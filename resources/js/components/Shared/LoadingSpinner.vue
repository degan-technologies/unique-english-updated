<template>
    <div class="flex items-center justify-center" :class="sizeClass">
        <svg
            class="animate-spin text-blue-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            :class="spinnerClass"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8h8a8 8 0 11-16 0z"
            ></path>
        </svg>
    </div>
</template>

<script setup>
import { computed } from "vue";

// Props for size
defineProps({
    size: {
        type: String,
        default: "md", // Options: sm, md, lg
    },
});

// Define size classes based on the size prop
const sizeClass = computed(() => {
    switch (size) {
        case "sm":
            return "h-6 w-6";
        case "lg":
            return "h-16 w-16";
        default:
            return "h-12 w-12";
    }
});

const spinnerClass = computed(() => {
    switch (size) {
        case "sm":
            return "h-6 w-6";
        case "lg":
            return "h-16 w-16";
        default:
            return "h-12 w-12";
    }
});
</script>
