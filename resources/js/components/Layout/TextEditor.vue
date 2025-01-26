<template>
    <div class="max-w-4xl mx-auto p-4">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4">PrimeVue Text Editor</h2>
            <Editor v-model="text" style="height: 300px" class="mb-4" />
            <div>
                <button
                    @click="saveText"
                    class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            text: "",
        };
    },
    methods: {
        saveText() {
            // You can replace this with your logic to save the text
            console.log("Saved Text:", this.text);
        },
    },
};
</script>

<style scoped>
/* Add any custom styling here */
</style>
