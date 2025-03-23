<script setup>
import Axios from "axios";
import Popper from "vue3-popper";
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { useAppStore } from "@/store/useAppStore";

const router = useRouter();
const appStore = useAppStore();
const cartStore = useCartStore();
const studentStore = UseStudentStore();

const checkoutUrl = ref(null);

const { items, itemCount, totalPrice } = storeToRefs(cartStore);
const { isLoggedIn, loggingIn, logoImage, authUser } = storeToRefs(appStore);
const { landingPageTab, selectedCourseSlug } = storeToRefs(studentStore);

const isMenuOpen = ref(false);
const isMenuVisible = ref(true);
const dropDownOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

function enrollCourse() {
    Axios
        .post("/api/initiate-payment", { cartItems: items.value })
        .then((res) => {
            checkoutUrl.value = res.data.checkout_url;
            window.open(checkoutUrl.value, "_blank");
        })
        .catch((error) => { });
}

function removeItem(item) {
    let selectedItem = {
        type: item.type,
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.thumbnail_url,
    };
    cartStore.removeFromCart(selectedItem);
}

function changeTab() {
    router.push({
        name: "student",
        query: {
            tab: landingPageTab.value,
        },
    });
    selectedCourseSlug.value = null;
}

function signOut() {
    appStore.setAuthToken('');
    loggingIn.value = false;
}

const getInitials = (name) => {
    if (!name) return "";
    return name.charAt(0).toUpperCase();
};

</script>

<template>
    <header
        class="fixed p-4 top-0 left-0 w-full z-10 transition-transform duration-300 ease-in-out  bg-lime-700 shadow-lg"
        :class="{
            'translate-y-0': isMenuVisible,
            '-translate-y-full': !isMenuVisible,
        }">
        <div class="  h-fit mx-auto flex items-center justify-between">
            <router-link to="/" class="flex items-center gap-2">
                <img src="/images/logo.jpg" alt="Logo"
                    class="h-12 w-12 rounded-full object-cover border-2 border-white" />
                <span class="text-xl font-bold text-white drop-shadow-md">UniqueEnglish</span>
            </router-link>

            <div class="flex gap-4">
                <!-- Cart Dropdown -->
                <div class="relative  self-center">
                    <Popper :offset-distance="'0'" placement="bottom-start">
                        <div class="relative text-2xl flex items-center self-center">
                            <i class="fa-solid fa-cart-plus mt-3 text-white cursor-pointer w-10 h-10"></i>
                            <span v-if="itemCount > 0"
                                class="  top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ itemCount }}
                            </span>
                        </div>
                        <template #content>
                            <div
                                class="  top-4 right-0 bg-white text-black shadow-lg w-96 h-96 overflow-y-auto scrollbar px-4 py-2 rounded">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-bold text-lg">Your Cart</h3>
                                </div>
                                <div v-if="!itemCount" class="h-20 flex justify-center items-center">
                                    <h1>No Item add to cart</h1>
                                </div>
                                <div v-else>
                                    <ul>
                                        <li v-for="item in items" :key="item.id"
                                            class="flex items-center justify-between mb-2 border-b pb-2">
                                            <img :src="item.image" alt="Item Image"
                                                class="h-12 w-12 object-cover rounded" />
                                            <div class="flex-1 ml-2">
                                                <span class="block font-semibold">{{ item?.name }}</span>
                                                <span class="text-gray-500 text-sm">${{ item?.price.toFixed(2) }}</span>
                                            </div>
                                            <i @click="removeItem(item)"
                                                class="fa-solid fa-minus text-red-500 hover:text-red-700"></i>
                                        </li>
                                    </ul>
                                    <div class="mt-4 font-bold text-lg">
                                        Total: ${{ totalPrice.toFixed(2) }}
                                    </div>
                                    <button @click="enrollCourse()"
                                        class="mt-2 bg-green-600 text-white px-4 py-2 rounded w-full hover:bg-green-700">
                                        Proceed to Checkout
                                    </button>
                                </div>
                            </div>
                        </template>
                    </Popper>
                </div> 
                
                <div v-if="isLoggedIn" class="relative self-center">
                    <img v-if="authUser?.profile" :src="authUser?.profile" alt="Profile Picture"
                        class="w-12 h-12 rounded-full shadow-lg cursor-pointer" @click="dropDownOpen = !dropDownOpen"
                        title="Profile" />

                    <div v-else @click="dropDownOpen = !dropDownOpen"
                        class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-lg font-bold text-gray-700">
                        {{ getInitials(authUser?.first_name) }}
                    </div>
                    <div v-if="dropDownOpen"
                        class="bg-gray-50 absolute  top-16 right-0 text-black w-48 shadow-lg rounded p-2">
                        <ul>
                            <li>
                                <router-link to="/my-course"
                                    class="block px-2 py-2 hover:bg-gray-200 text-sm  items-center gap-2">
                                    <span class="block px-4 py-2 hover:bg-gray-200">My Courses</span>
                                </router-link>
                            </li>
                            <li>
                                <a href="/certificate"
                                    class="block px-2 py-2 hover:bg-gray-200 text-sm  items-center gap-2">
                                    <span class="block px-4 py-2 hover:bg-gray-200">Certificate</span>
                                </a>
                            </li>
                        </ul>
                        <hr class="my-2 border-gray-300" />
                        <ul>
                            <li>
                                <a href="#" @click.prevent="signOut"
                                    class="block px-2 py-2 hover:bg-red-100 text-red-500 text-sm round items-center gap-2">
                                    <span>Sign Out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-else class="flex gap-4">
                    <router-link to="/login" class="btn-login">Login</router-link>
                    <router-link to="/register" class="btn-register">Register</router-link>
                </div>
                <button
                    class="text-white h-fit w-fit text-sm px-2 py-1 rounded-md mt-3 border-2 border-yellow-400  font-bold hover:border-yellow-600">አማርኛ</button>

            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="toggleMenu" class="block md:hidden text-2xl text-white focus:outline-none">
                <span v-if="!isMenuOpen">☰</span>
                <span v-else>✖</span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <transition name="mobile-menu">
            <div v-if="isMenuOpen" class="md:hidden bg-lime-600 text-white shadow-lg rounded-b-xl p-4">
                <ul class="flex flex-col items-center gap-4">
                    <li>
                        <a href="#home" class="mobile-link" @click="toggleMenu">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="#about" class="mobile-link" @click="toggleMenu">
                            About
                        </a>
                    </li>
                    <li>
                        <a href="#courses" class="mobile-link" @click="toggleMenu">
                            Courses
                        </a>
                    </li>
                    <li>
                        <a href="#contact" class="mobile-link" @click="toggleMenu">
                            Contact
                        </a>
                    </li>
                    <div class="flex gap-4">
                        <router-link to="/login" class="btn-login" @click="toggleMenu">
                            Login
                        </router-link>
                        <router-link to="/register" class="btn-register" @click="toggleMenu">
                            Register
                        </router-link>
                    </div>
                </ul>
            </div>
        </transition>

    </header>
</template>


<style scoped>
/* Glassmorphic Header Base */
header {
    transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
}

/* Navigation Links */
.nav-link {
    color: white;
    transition: color 0.3s ease;
}

.nav-link:hover {
    color: #d1fae5;
}

/* Call-to-Action Buttons */
.btn-login,
.btn-register {
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    outline: none;
    border: none;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-login {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.btn-login:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 0.3);
}

.btn-register {
    background: linear-gradient(135deg, #84cc16, #65a30d);
    color: white;
}

.btn-register:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    filter: brightness(1.1);
}

/* Cart Dropdown */
.cart-dropdown {
    position: absolute;
    top: 12px;
    right: 0;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 16px;
    width: 320px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.cart-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.checkout-btn {
    width: 100%;
    background: #65a30d;
    padding: 10px;
    border-radius: 8px;
    color: white;
    font-weight: bold;
    text-align: center;
    transition: background 0.3s ease;
}

.checkout-btn:hover {
    background: #4d7c0f;
}

/* Mobile Menu */
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

.mobile-link {
    color: white;
    transition: color 0.3s ease;
}

.mobile-link:hover {
    color: #d1fae5;
}
</style>
