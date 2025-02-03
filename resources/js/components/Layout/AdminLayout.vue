<template>
    <div class="flex h-screen bg-gray-100">
        <div class="w-64 bg-gray-800 text-white p-4">
            <div class="text-2xl font-semibold mb-6">Admin Dashboard</div>
            <ul>
                <li>
                    <router-link
                        to="/dashboard"
                        class="block py-2 hover:bg-gray-700"
                        >Dashboard</router-link
                    >
                </li>
                <li>
                    <router-link
                        to="/users"
                        class="block py-2 hover:bg-gray-700"
                        >Users</router-link
                    >
                </li>
                <li>
                    <router-link
                        to="/courses"
                        class="block py-2 hover:bg-gray-700"
                        >Courses</router-link
                    >
                </li>
            </ul>
        </div>
        <div class="flex-1 p-6">
            <router-view />
        </div>
    </div>
</template>

<script>
export default {
    name: "AdminLayout",
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
