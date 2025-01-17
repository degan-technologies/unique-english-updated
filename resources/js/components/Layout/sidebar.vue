<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            :class="{
                'fixed inset-y-0 left-0 z-20 w-64 bg-blue-600 text-white transform transition-transform duration-300 ease-in-out': true,
                'translate-x-0': isSidebarOpen,
                '-translate-x-full': !isSidebarOpen,
            }"
            class="md:static md:translate-x-0"
        >
            <div class="p-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">MyApp</h2>
                <button
                    @click="toggleSidebar"
                    class="text-2xl md:hidden focus:outline-none"
                >
                    ✖
                </button>
            </div>
            <nav class="mt-4">
                <ul class="space-y-4">
                    <li>
                        <a
                            href="#home"
                            class="block px-4 py-2 hover:bg-blue-500 rounded"
                            >Home</a
                        >
                    </li>
                    <li>
                        <a
                            href="#about"
                            class="block px-4 py-2 hover:bg-blue-500 rounded"
                            >About</a
                        >
                    </li>
                    <li>
                        <a
                            href="#services"
                            class="block px-4 py-2 hover:bg-blue-500 rounded"
                            >Services</a
                        >
                    </li>
                    <li>
                        <a
                            href="#contact"
                            class="block px-4 py-2 hover:bg-blue-500 rounded"
                            >Contact</a
                        >
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header for Mobile -->
            <header
                class="p-4 bg-white shadow-md md:hidden flex justify-between items-center"
            >
                <h2 class="text-xl font-bold">MyApp</h2>
                <button
                    @click="toggleSidebar"
                    class="text-2xl focus:outline-none"
                >
                    ☰
                </button>
            </header>

            <!-- Main Content Area -->
            <main class="p-6">
                <h1 class="text-3xl font-bold mb-4">Welcome to MyApp!</h1>
                <p>
                    This is the main content area. Resize the window or toggle
                    the menu to see the responsive behavior of the sidebar.
                </p>
            </main>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";

export default {
    name: "ResponsiveSidebar",
    setup() {
        const isSidebarOpen = ref(false);

        const toggleSidebar = () => {
            isSidebarOpen.value = !isSidebarOpen.value;
        };

        return { isSidebarOpen, toggleSidebar };
    },
};
</script>

<style scoped>
@media (max-width: 768px) {
    body {
        overflow: hidden;
    }
}
</style>
