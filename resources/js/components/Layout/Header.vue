<script setup>
import Axios from "axios";
import Popper from "vue3-popper";
import { storeToRefs } from "pinia";
import { onMounted, ref, watch, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";

import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const router = useRouter();
const appStore = useAppStore();
const cartStore = useCartStore();
const AuthStore = useAuthStore();
const studentStore = UseStudentStore();

const checkoutUrl = ref(null);

// Reactive refs from Pinia stores
const { items, itemCount, totalPrice } = storeToRefs(cartStore);
const { showLoginForm, showRegistrationForm } = storeToRefs(AuthStore);
const { isLoggedIn, loggingIn, logoImage, authUser, exploreCourses } =
    storeToRefs(appStore);
const { landingPageTab, selectedCourseSlug, myCourseTab } =
    storeToRefs(studentStore);

// UI state refs
const dropdownRef = ref(null);
const profileBtnRef = ref(null);
const isMenuOpen = ref(false);
const isMenuVisible = ref(true);
const dropDownOpen = ref(false);
const actionTypeLogin = ref("login");
const actionTypeRegister = ref("register");

// Toggle mobile menu
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

// Close profile dropdown when clicking outside
function handleClickOutside(event) {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target) &&
        profileBtnRef.value &&
        !profileBtnRef.value.contains(event.target)
    ) {
        dropDownOpen.value = false;
    }
}

// Navigate to explore courses
function onExploreCourses() {
    router.push("/courses").then(() => {
        exploreCourses.value = !exploreCourses.value;
    });
}

// Initiate checkout
function enrollCourse() {
    Axios.post("/api/initiate-payment", { cartItems: items.value })
        .then((res) => {
            checkoutUrl.value = res.data.checkout_url;
            window.open(checkoutUrl.value, "_blank");
        })
        .catch((error) => {
            console.error(error);
        });
}

// Remove a single item
function removeItem(item) {
    const selectedItem = {
        type: item.type,
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.thumbnail_url,
    };
    cartStore.removeFromCart(selectedItem);
}

// Switch to “My Courses” tab
function changeTab() {
    router.push({
        name: "student",
        query: { tab: myCourseTab.value },
    });
}

// Sign out user
function signOut() {
    appStore.setAuthToken("");
    loggingIn.value = false;
}

// Get initials for placeholder avatar
const getInitials = (name) => {
    if (!name) return "";
    return name.charAt(0).toUpperCase();
};

// Toggle login/register forms
function toggleAuthActions(actionType) {
    if (actionType === actionTypeLogin.value) {
        showLoginForm.value = true;
        showRegistrationForm.value = false;
    } else {
        showLoginForm.value = false;
        showRegistrationForm.value = true;
    }
}

onMounted(() => {
    document.addEventListener("click", handleClickOutside);

    // Load cart from localStorage
    const saved = localStorage.getItem("cartItems");
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            cartStore.setCart(parsed);
        } catch (e) {
            console.warn("Failed to parse cartItems from localStorage:", e);
        }
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

watch(
    items,
    (newItems) => {
        localStorage.setItem("cartItems", JSON.stringify(newItems));
    },
    { deep: true }
);
</script>

<template>
    <header
        class="fixed top-0 left-0 w-full z-10 p-2 bg-lime-700 shadow-lg transition-transform duration-300 ease-in-out"
        :class="{
            'translate-y-0': isMenuVisible,
            '-translate-y-full': !isMenuVisible,
        }"
    >
        <div class="mx-auto flex items-center justify-between h-fit">
            <router-link to="/" class="flex items-center gap-2">
                <img
                    src="/images/logo.jpg"
                    alt="Logo"
                    class="h-12 w-12 rounded-full object-cover border-2 border-white"
                />
                <span
                    class="text-xl font-bold text-white drop-shadow-md hidden sm:inline-block"
                >
                    Unique English
                </span>
            </router-link>

            <div class="flex items-center gap-4">
                <!-- Cart Dropdown -->
                <div class="relative flex justify-center">
                    <Popper Popper :offset-distance="'0'" placement="bottom">
                        <!-- Cart Icon with Count -->
                        <div
                            class="relative flex justify-center items-center text-2xl cursor-pointer"
                        >
                            <div class="relative">
                                <i
                                    class="fa-solid fa-cart-plus text-white text-2xl"
                                ></i>
                                <span
                                    v-if="itemCount > 0"
                                    class="absolute -top-2 -right-2 w-5 h-5 flex items-center justify-center text-xs font-bold text-white bg-red-500 rounded-full border border-white shadow"
                                >
                                    {{ itemCount }}
                                </span>
                            </div>
                        </div>

                        <!-- Dropdown -->
                        <template #content>
                            <div
                                class="z-50 w-screen max-w-screen px-4 sm:px-0 sm:w-[450px] sm:max-w-lg sm:shadow-2xl mt-4"
                            >
                                <div
                                    class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 max-h-[80vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300"
                                >
                                    <!-- Header -->
                                    <div class="text-center border-b pb-3 mb-3">
                                        <h3
                                            class="text-xl font-semibold text-gray-700"
                                        >
                                            Your Cart
                                        </h3>
                                    </div>

                                    <!-- Empty Cart -->
                                    <div
                                        v-if="!itemCount"
                                        class="flex flex-col items-center justify-center h-40 space-y-3 text-gray-500"
                                    >
                                        <i
                                            class="fa-solid fa-cart-shopping fa-fade text-4xl text-gray-400"
                                        ></i>
                                        <h1 class="text-center text-base">
                                            No items in cart
                                        </h1>
                                        <button
                                            @click="onExploreCourses"
                                            class="bg-lime-600 hover:bg-lime-700 text-white py-2 px-4 rounded-lg font-medium text-sm transition"
                                        >
                                            <i
                                                class="fa-solid fa-book-open-reader mr-2"
                                            ></i
                                            >Explore Courses
                                        </button>
                                    </div>

                                    <!-- Cart Items -->
                                    <div v-else>
                                        <ul class="space-y-3">
                                            <li
                                                v-for="item in items"
                                                :key="item.id"
                                                class="flex items-center justify-between gap-3 p-2 rounded hover:bg-gray-50 transition"
                                            >
                                                <img
                                                    :src="item.image"
                                                    alt="Item Image"
                                                    class="w-14 h-14 rounded-lg object-cover border"
                                                />
                                                <div class="flex-1">
                                                    <p
                                                        class="font-medium text-sm truncate"
                                                    >
                                                        {{ item.name }}
                                                    </p>
                                                    <p
                                                        class="text-gray-500 text-sm"
                                                    >
                                                        ${{
                                                            item.price.toFixed(
                                                                2
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <i
                                                    @click="removeItem(item)"
                                                    class="fa-solid fa-minus text-red-500 hover:text-red-700 cursor-pointer"
                                                ></i>
                                            </li>
                                        </ul>

                                        <!-- Footer -->
                                        <div class="mt-5 border-t pt-3">
                                            <div
                                                class="flex justify-between text-base font-semibold"
                                            >
                                                <span>Total:</span>
                                                <span
                                                    >${{
                                                        totalPrice.toFixed(2)
                                                    }}</span
                                                >
                                            </div>

                                            <button
                                                @click="enrollCourse()"
                                                class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-semibold transition"
                                            >
                                                Proceed to Checkout
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Popper>
                </div>

                <!-- Auth/Profile -->
                <div v-if="isLoggedIn" class="relative flex justify-center">
                    <!-- Profile Image or Initials -->
                    <img
                        v-if="authUser?.profile"
                        :src="authUser.profile"
                        alt="Profile"
                        ref="profileBtnRef"
                        @click="dropDownOpen = !dropDownOpen"
                        title="Profile"
                        class="w-8 h-8 rounded-full shadow-lg cursor-pointer"
                    />
                    <div
                        v-else
                        ref="profileBtnRef"
                        @click="dropDownOpen = !dropDownOpen"
                        class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-300 text-lg font-bold text-gray-700 cursor-pointer"
                    >
                        {{ getInitials(authUser?.first_name) }}
                    </div>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="dropDownOpen"
                        ref="dropdownRef"
                        class="absolute top-12 left-1/2 transform -translate-x-1/2 w-56 p-2 bg-white text-black rounded-lg shadow-xl z-50"
                    >
                        <ul>
                            <li>
                                <button
                                    @click="
                                        changeTab();
                                        dropDownOpen = false;
                                    "
                                    class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 rounded"
                                >
                                    <i class="fas fa-book text-gray-500"></i> My
                                    Courses
                                </button>
                            </li>
                            <!-- <li>
                                <a
                                    href="/certificate"
                                    class="block w-full px-4 py-2 text-sm hover:bg-gray-100 rounded"
                                >
                                    <i
                                        class="fas fa-certificate text-gray-500"
                                    ></i>
                                    Certificate
                                </a>
                            </li> -->
                        </ul>
                        <hr class="my-2 border-gray-300" />
                        <ul>
                            <li>
                                <button
                                    @click.prevent="signOut"
                                    class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 rounded flex items-center gap-2"
                                >
                                    <i class="fas fa-right-from-bracket"></i>
                                    Sign Out
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Login/Register -->
                <div v-else class="hidden md:flex gap-4">
                    <button
                        @click="toggleAuthActions(actionTypeLogin)"
                        class="px-2 py-1 font-semibold text-white rounded-md bg-white/20 border border-white/30 hover:bg-white/30 transform transition duration-200 ease-in-out shadow-sm"
                    >
                        Login
                    </button>
                    <button
                        @click="toggleAuthActions(actionTypeRegister)"
                        class="px-2 py-1 font-semibold text-white rounded-md bg-gradient-to-br from-lime-400 to-lime-700 hover:brightness-110 transform transition duration-200 ease-in-out shadow-sm"
                    >
                        Register
                    </button>
                </div>

                <!-- Language Toggle -->
                <button
                    class="px-2 text-sm font-bold text-white border-2 border-yellow-400 rounded hover:border-yellow-600"
                >
                    አማ
                </button>
            </div>

            <!-- Mobile Menu Toggle -->
            <button
                @click="toggleMenu"
                class="block md:hidden text-2xl text-white focus:outline-none"
            >
                <i
                    :class="
                        isMenuOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'
                    "
                ></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <transition name="mobile-menu">
            <div
                v-if="isMenuOpen"
                class="p-4 text-white border-t bottom-3 w-full mt-3 md:hidden"
            >
                <ul class="flex flex-col items-center gap-4">
                    <li>
                        <a
                            href="/"
                            @click="toggleMenu"
                            class="text-white hover:text-lime-200"
                        >
                            Home
                        </a>
                    </li>
                    <li>
                        <a
                            href="#about"
                            @click="toggleMenu"
                            class="text-white hover:text-lime-200"
                        >
                            About
                        </a>
                    </li>
                    <li>
                        <a
                            href="#courses"
                            @click="toggleMenu"
                            class="text-white hover:text-lime-200"
                        >
                            Courses
                        </a>
                    </li>
                    <li>
                        <a
                            href="#books"
                            @click="toggleMenu"
                            class="text-white hover:text-lime-200 mt-5"
                        >
                            Books
                        </a>
                    </li>

                    <!-- Simplified auth section -->
                    <template v-if="!isLoggedIn">
                        <div class="flex gap-4">
                            <button
                                @click="toggleAuthActions(actionTypeLogin)"
                                class="px-2 py-1 font-semibold text-white rounded-md bg-white/20 border border-white/30 hover:bg-white/30 transform transition duration-200 ease-in-out shadow-sm"
                            >
                                Login
                            </button>
                            <button
                                @click="toggleAuthActions(actionTypeRegister)"
                                class="px-2 py-1 font-semibold text-white rounded-md bg-gradient-to-br from-lime-400 to-lime-700 hover:brightness-110 transform transition duration-200 ease-in-out shadow-sm"
                            >
                                Register
                            </button>
                        </div>
                    </template>
                </ul>
            </div>
        </transition>
    </header>
</template>
