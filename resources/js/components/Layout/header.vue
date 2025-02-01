<script>
import Axios from "axios";
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useCartStore } from "../../store/cart";
import { useAppStore } from "../../store/useAppStore"; // ✅ Correct import
import { useRouter } from "vue-router";

export default {
    name: "Header",
    setup() {
        const appStore = useAppStore(); // ✅ Call store inside setup()
        const cartStore = useCartStore();
        const isMenuOpen = ref(false);
        const isMenuVisible = ref(true);
        const isCartModalVisible = ref(false);
        const router = useRouter();

        const toggleMenu = () => {
            isMenuOpen.value = !isMenuOpen.value;
        };

        const handleScroll = () => {
            const currentScroll = window.pageYOffset;
            isMenuVisible.value = currentScroll <= 0 || currentScroll > window.prevScroll;
            window.prevScroll = currentScroll;
        };

        const toggleCartModal = (state) => {
            isCartModalVisible.value = state;
        };

        const totalCartPrice = computed(() => {
            return cartStore.items.reduce((total, item) => total + item.price, 0);
        });

        const proceedToCheckout = async () => {
    try {
        if (!appStore.authUser) { 
            alert("Please log in to proceed with payment.");
            return;
        }

        const response = await Axios.post("http://127.0.0.1:8000/api/initiate-payment", {
            amount: totalCartPrice.value,
            email: appStore.authUser.email,
            first_name: appStore.authUser.first_name, // ✅ Include first_name
            last_name: appStore.authUser.last_name, // ✅ Include last_name
        });

        if (response.data.payment_url) {
            window.location.href = response.data.payment_url;
        } else {
            
            alert("Failed to initiate payment.");
        }
            } catch (error) {
                console.error("Payment initiation failed:", error.response ? error.response.data : error.message);
                alert(`An error occurred while processing your payment: ${error.response ? JSON.stringify(error.response.data) : error.message}`);
            }
        };


        onMounted(() => {
            window.prevScroll = 0;
            window.addEventListener("scroll", handleScroll);
        });

        onUnmounted(() => {
            window.removeEventListener("scroll", handleScroll);
        });

        return {
            isMenuOpen,
            isMenuVisible,
            isCartModalVisible,
            toggleMenu,
            cartStore,
            toggleCartModal,
            totalCartPrice,
            proceedToCheckout,
            appStore, // ✅ Return store to use it in template
        };
    },
};
</script>



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

						<div class="relative">
                <button @click="toggleCartModal(true)" class="relative text-2xl flex items-center">
                    <span class="material-icons text-white">shopping_cart</span>
                    <span v-if="cartStore.itemCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ cartStore.itemCount }}
                    </span>
                </button>

                <div v-if="isCartModalVisible" class="absolute top-12 right-0 bg-white text-black shadow-lg w-72 p-4 rounded">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-lg">Your Cart</h3>
                        <button @click="toggleCartModal(false)" class="text-red-500 hover:text-red-700">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <div v-if="cartStore.items.length === 0">
                        <p>Your cart is empty.</p>
                    </div>
                    <div v-else>
                        <ul>
                            <li v-for="item in cartStore.items" :key="item.id" class="flex items-center justify-between mb-2 border-b pb-2">
                                <img :src="item.image" alt="Item Image" class="h-12 w-12 object-cover rounded" />
                                <div class="flex-1 ml-2">
                                    <span class="block font-semibold">{{ item.name }}</span>
                                    <span class="text-gray-500 text-sm">${{ item.price.toFixed(2) }}</span>
                                </div>
                                <button @click="cartStore.removeFromCart(item.id)" class="text-red-500 hover:text-red-700">
                                    <span class="material-icons">remove_circle</span>
                                </button>
                            </li>
                        </ul>
                        <div class="mt-4 font-bold text-lg">Total: ${{ totalCartPrice.toFixed(2) }}</div>
                        <button @click="proceedToCheckout" class="mt-2 bg-green-600 text-white px-4 py-2 rounded w-full hover:bg-green-700">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>

            <button @click="toggleMenu" class="block md:hidden text-2xl focus:outline-none">
                <span v-if="!isMenuOpen">☰</span>
                <span v-else>✖</span>
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
