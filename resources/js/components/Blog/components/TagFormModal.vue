<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">
                    {{ editing ? "Edit" : "Create" }} Tag
                </h2>
                <button
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                        >Tag Name *</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none"
                        placeholder="e.g., Grammar, Vocabulary"
                    />
                </div>

                <div class="flex gap-4 pt-4">
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-6 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 disabled:opacity-50"
                    >
                        {{
                            submitting
                                ? "Saving..."
                                : editing
                                  ? "Update Tag"
                                  : "Create Tag"
                        }}
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    editing: Object,
});

const emit = defineEmits(["close", "save"]);

const form = ref({ name: "" });
const submitting = ref(false);

watch(
    () => props.editing,
    (tag) => {
        if (tag) {
            form.value = { name: tag.name };
        } else {
            form.value = { name: "" };
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    submitting.value = true;
    emit("save", form.value);
    submitting.value = false;
};
</script>
