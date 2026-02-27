<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <svg
                    class="w-6 h-6 text-red-600 mr-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                    />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">
                    Confirm Deletion
                </h3>
            </div>
            <p class="text-gray-600 mb-6">{{ message }}</p>
            <div class="flex gap-4">
                <button
                    @click="$emit('confirm')"
                    :disabled="submitting"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                >
                    {{ submitting ? "Deleting..." : "Delete" }}
                </button>
                <button
                    @click="$emit('cancel')"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    message: String,
    submitting: Boolean,
});

defineEmits(["confirm", "cancel"]);
</script>
