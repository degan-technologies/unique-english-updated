<template>
    <div>
        <!-- Button to Open Modal -->
        <button
            @click="openModal"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
            Open Modal
        </button>

        <!-- Modal Backdrop -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
        >
            <!-- Modal Dialog -->
            <div
                class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4 sm:mx-auto p-6 z-50"
            >
                <!-- Modal Header -->
                <div
                    class="flex justify-between items-center border-b pb-2 mb-4"
                >
                    <h2 class="text-xl font-bold">Modal Title</h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition text-2xl"
                    >
                        &times;
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="text-gray-700">
                    <p>
                        This is a responsive modal built with Vue.js and
                        Tailwind CSS. Resize the window to see its behavior on
                        different screen sizes.
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="mt-4 flex justify-end gap-4">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition"
                    >
                        Cancel
                    </button>
                    <button
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";

export default {
    name: "Modal",
    setup() {
        const isModalOpen = ref(false);

        const openModal = () => {
            isModalOpen.value = true;
        };

        const closeModal = () => {
            isModalOpen.value = false;
        };

        return { isModalOpen, openModal, closeModal };
    },
};
</script>

<style scoped>
/* Disable scrolling when modal is open */
body {
    overflow: hidden;
}
</style>
