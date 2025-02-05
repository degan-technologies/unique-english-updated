<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            Admin Dashboard
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-500 text-white p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-medium">Total Users</h3>
                <p class="text-2xl">1234</p>
            </div>
            <div class="bg-green-500 text-white p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-medium">Total Courses</h3>
                <p class="text-2xl">45</p>
            </div>
            <div class="bg-yellow-500 text-white p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-medium">Revenue</h3>
                <p class="text-2xl">$12,345</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Dashboard",
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
