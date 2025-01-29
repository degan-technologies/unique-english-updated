<template>
    <header
        class="bg-lime-700 text-white shadow-md fixed top-0 left-0 w-full z-10 transition-transform duration-300 ease-in-out"
        :class="{
            'translate-y-0': isMenuVisible,
            '-translate-y-full': !isMenuVisible,
        }"
    >
        <nav class="container mx-auto flex items-center justify-between p-4">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2">
                <img
                    src="/images/logo.jpg"
                    alt="Logo"
                    class="h-12 w-12 rounded-full object-cover border-2 border-white"
                />
                <span class="text-xl font-bold">UniqueEnglish</span>
            </a>

            <!-- Desktop Menu -->
            <ul class="hidden md:flex gap-6">
                <li>
                    <a href="#home" class="hover:text-gray-300">Home</a>
                </li>
                <li>
                    <a href="#about" class="hover:text-gray-300">About</a>
                </li>
                <li>
                    <a href="#courses" class="hover:text-gray-300">Courses</a>
                </li>
                <li>
                    <a href="#contact" class="hover:text-gray-300">Contact</a>
                </li>
            </ul>

            <!-- Mobile Menu Button -->
            <button
                @click="toggleMenu"
                class="block md:hidden text-2xl focus:outline-none"
                :aria-expanded="isMenuOpen"
                aria-label="Toggle navigation menu"
            >
                <i :class="isMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
            </button>
        </nav>

        <!-- Mobile Menu -->
        <transition name="mobile-menu">
            <div
                v-if="isMenuOpen"
                class="md:hidden bg-lime-600 text-white shadow-lg"
            >
                <ul class="flex flex-col items-center gap-4 py-4">
                    <li>
                        <a
                            href="#home"
                            class="hover:text-gray-300"
                            @click="closeMenu"
                        >
                            Home
                        </a>
                    </li>
                    <li>
                        <a
                            href="#about"
                            class="hover:text-gray-300"
                            @click="closeMenu"
                        >
                            About
                        </a>
                    </li>
                    <li>
                        <a
                            href="#courses"
                            class="hover:text-gray-300"
                            @click="closeMenu"
                        >
                            Courses
                        </a>
                    </li>
                    <li>
                        <a
                            href="#contact"
                            class="hover:text-gray-300"
                            @click="closeMenu"
                        >
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
        </transition>
    </header>
</template>

<script>
import { ref, onMounted, onUnmounted } from "vue";

export default {
    name: "Header",
    setup() {
        const isMenuOpen = ref(false);
        const isMenuVisible = ref(true);

        const toggleMenu = () => {
            isMenuOpen.value = !isMenuOpen.value;
        };

        const closeMenu = () => {
            isMenuOpen.value = false;
        };

        const handleScroll = () => {
            // Show or hide the header based on scroll direction
            const currentScroll = window.pageYOffset;
            isMenuVisible.value =
                currentScroll <= 0 || currentScroll > window.prevScroll;
            window.prevScroll = currentScroll;
        };

        onMounted(() => {
            window.prevScroll = 0;
            window.addEventListener("scroll", handleScroll);
        });

        onUnmounted(() => {
            window.removeEventListener("scroll", handleScroll);
        });

        return { isMenuOpen, isMenuVisible, toggleMenu, closeMenu };
    },
};
</script>

<style scoped>
/* Smooth transition for header visibility */
header {
    transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
}

/* Smooth transition for the mobile menu */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
    transform: translateY(-10%);
    opacity: 0;
}

.mobile-menu-enter-to,
.mobile-menu-leave-from {
    transform: translateY(0);
    opacity: 1;
}
</style>
