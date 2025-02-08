<template>
    <div class="w-full border-t-2 px-6 py-4 flex flex-col lg:flex-row justify-between items-center text-gray-600">
        <p class="mb-2 lg:mb-0">© Copyright {{ currentYear }}</p>
        <div class="flex space-x-4">
            <a href="#" class="hover:text-gray-900">Terms of Service</a>
            <a href="#" class="hover:text-gray-900">Privacy Policy</a>
            <a href="#" class="hover:text-gray-900">About Us</a>
        </div>
    </div>
</template>

<script>
export default {
    name: "Footer",
    computed: {
        currentYear() {
            return new Date().getFullYear();
        }
    }
};
</script>
