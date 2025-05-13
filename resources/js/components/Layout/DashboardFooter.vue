<template>
    <div
        class="w-full border-t-2 px-6 py-4 flex flex-col lg:flex-row justify-between items-center text-gray-600"
    >
        <p class="mb-2 lg:mb-0">© Copyright {{ currentYear }}</p>
    </div>
</template>

<script>
export default {
    name: "Footer",
    computed: {
        currentYear() {
            return new Date().getFullYear();
        },
    },
};
</script>
