<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import Popper from "vue3-popper";

import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { useSidebarStore } from "@/store/useSidebarStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const router = useRouter();

// Pinia stores
const sidebarStore = useSidebarStore();
const { selectedContent } = storeToRefs(sidebarStore);

const appStore = useAppStore();
const AuthStore = useAuthStore();
const cartStore = useCartStore();
const studentStore = UseStudentStore();

// Cart refs
const { items, itemCount, totalPrice } = storeToRefs(cartStore);

// Auth refs
const { showLoginForm, showRegistrationForm } = storeToRefs(AuthStore);

// App & user refs
const {
    isLoggedIn,
    loggingIn,
    logoImage,
    unreadNotifications,
    notifications,
    readNotifications,
    authUser,
    exploreCourses,
} = storeToRefs(appStore);

// Student refs
const { landingPageTab, selectedCourseSlug, myCourseTab } =
    storeToRefs(studentStore);

// Notification refs
const notificationOpen = ref(false);
const showAllNotifications = ref(false);
const currentNotification = ref(null);
const showNotificationModal = ref(false);

// UI state refs
const isCartOpen = ref(false);
const isMenuOpen = ref(false);
const isMenuVisible = ref(true);

// --- Profile dropdown refs ---
const dropDownOpen = ref(false);
const profileBtnRef = ref(null);
const dropdownRef = ref(null);

// Action type refs
const actionTypeLogin = ref("login");
const actionTypeRegister = ref("register");

// Checkout
const checkoutUrl = ref(null);
const isLoading = ref(false);

// Helpers
function formatTime(date) {
    const options = {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };
    return new Date(date).toLocaleDateString(undefined, options);
}

// Toggle notifications dropdown
const toggleNotifications = () => {
    notificationOpen.value = !notificationOpen.value;
    dropDownOpen.value = false;
    if (notificationOpen.value) {
        appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            appStore.fetchReadNotifications();
        }
    }
};

async function markAsRead(notificationId) {
    try {
        await appStore.markNotificationAsRead(notificationId);
        await appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            await appStore.fetchReadNotifications();
        }
    } catch (e) {
        console.error("Error marking notification as read:", e);
    }
}

async function markAsUnread(notificationId) {
    try {
        await appStore.markNotificationAsUnread(notificationId);
        await appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            await appStore.fetchReadNotifications();
        }
    } catch (e) {
        console.error("Error marking notification as unread:", e);
    }
}

function showNotificationDetails(notification) {
    currentNotification.value = notification;
    showNotificationModal.value = true;
    notificationOpen.value = false;

    if (notification.data.read_at == null) {
        appStore.markNotificationAsRead(notification.id);
    }
}

function closeNotificationModal() {
    showNotificationModal.value = false;
}

function toggleShowAllNotifications() {
    if (notifications.length === 0) return;
    showAllNotifications.value = !showAllNotifications.value;
}

// Mobile menu
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

// --- Profile dropdown logic ---
function toggleDropdown() {
    dropDownOpen.value = !dropDownOpen.value;
    notificationOpen.value = false;
}
function closeDropdown() {
    dropDownOpen.value = false;
}
function handleClickOutside(event) {
    if (
        dropDownOpen.value &&
        profileBtnRef.value &&
        dropdownRef.value &&
        !profileBtnRef.value.contains(event.target) &&
        !dropdownRef.value.contains(event.target)
    ) {
        closeDropdown();
    }
}

// Get initials for fallback avatar
const getInitials = (name) => (name ? name.charAt(0).toUpperCase() : "");

// Navigation actions
function onExploreCourses() {
    isCartOpen.value = false;
    router.push("/").then(() => {
        exploreCourses.value = !exploreCourses.value;
    });
}

// Checkout flow
async function enrollCourse() {
    if (isLoading.value) return;
    isLoading.value = true;
    try {
        const res = await Axios.post("/api/initiate-payment", {
            cartItems: [...items.value],
        });
        checkoutUrl.value = res.data.checkout_url;
        cartStore.clearCart();
        localStorage.removeItem("cartItems");
        const newWin = window.open(checkoutUrl.value, "_blank");
        if (newWin) newWin.focus();
    } catch (e) {
        console.error("Checkout error:", e);
    } finally {
        isLoading.value = false;
    }
}

function removeItem(item) {
    cartStore.removeFromCart({
        type: item.type,
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.thumbnail_url,
    });
}

function changeTab() {
    router.push({
        name: "student",
        query: { tab: myCourseTab.value },
    });
}

function openProfile() {
    router.push({
        name: "student",
        query: { currentTab: "profile" },
    });
    closeDropdown();
}

function signOut() {
    appStore.setAuthToken("");
    loggingIn.value = false;
    closeDropdown();
}

function toggleAuthActions(actionType) {
    if (actionType === actionTypeLogin.value) {
        showLoginForm.value = true;
        showRegistrationForm.value = false;
    } else {
        showLoginForm.value = false;
        showRegistrationForm.value = true;
    }
}

// Lifecycle
onMounted(() => {
    document.addEventListener("click", handleClickOutside);

    // cart persistence
    const saved = localStorage.getItem("cartItems");
    if (saved) {
        try {
            cartStore.setCart(JSON.parse(saved));
        } catch {
            localStorage.removeItem("cartItems");
        }
    }

    // notifications
    appStore.fetchUnreadNotifications();
    if (window.Echo && authUser.value?.id) {
        window.Echo.private(
            `App.Models.User.${authUser.value.id}`,
        ).notification(() => {
            appStore.fetchUnreadNotifications();
            if (showAllNotifications.value) {
                appStore.fetchReadNotifications();
            }
        });
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
    { deep: true },
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

            <div class="flex items-center gap-4 pr-4">
                <!-- Notification Button - Only show if logged in -->
                <div v-if="isLoggedIn" class="relative flex justify-center">
                    <Popper
                        v-model:visible="notificationOpen"
                        :offset-distance="'0'"
                        placement="bottom"
                    >
                        <!-- Notification Icon with Count -->
                        <div
                            class="relative flex justify-center items-center text-2xl cursor-pointer"
                            @click="toggleNotifications"
                        >
                            <div class="relative">
                                <i
                                    class="fa-solid fa-bell text-2xl text-white"
                                ></i>
                                <span
                                    v-if="unreadNotifications > 0"
                                    class="absolute -top-2 -right-2 w-5 h-5 flex items-center justify-center text-xs font-bold text-white bg-red-500 rounded-full border border-white shadow"
                                >
                                    {{ unreadNotifications }}
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
                                    <div
                                        class="border-b pb-3 mb-3 flex justify-between items-center"
                                    >
                                        <h3
                                            class="text-xl font-semibold text-gray-700"
                                        >
                                            Notifications
                                        </h3>
                                        <button
                                            @click.stop="
                                                toggleShowAllNotifications
                                            "
                                            class="text-xs text-lime-600 hover:text-lime-800"
                                        >
                                            {{
                                                showAllNotifications
                                                    ? "Show Unread Only"
                                                    : "Show All"
                                            }}
                                        </button>
                                    </div>

                                    <!-- No notifications -->
                                    <div
                                        v-if="
                                            notifications.length === 0 &&
                                            (!showAllNotifications ||
                                                readNotifications.length === 0)
                                        "
                                        class="p-4 text-gray-500 text-sm flex flex-col items-center justify-center h-40"
                                    >
                                        <i
                                            class="fa-regular fa-bell-slash text-4xl text-gray-400 mb-3"
                                        ></i>
                                        <p>No new notifications</p>
                                    </div>

                                    <!-- Unread Notifications -->
                                    <ul
                                        v-if="notifications.length > 0"
                                        class="space-y-2"
                                    >
                                        <li
                                            v-for="notification in notifications"
                                            :key="notification.id"
                                            :class="{
                                                hidden:
                                                    showAllNotifications &&
                                                    notification.data.read_at,
                                            }"
                                            class="p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition"
                                            @click="
                                                showNotificationDetails(
                                                    notification,
                                                )
                                            "
                                        >
                                            <div class="flex items-start gap-3">
                                                <div>
                                                    <p
                                                        class="font-medium text-sm"
                                                    >
                                                        {{
                                                            notification.data
                                                                .message
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-500 mt-1"
                                                    >
                                                        {{
                                                            formatTime(
                                                                notification.created_at,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <span
                                                    v-if="
                                                        currentNotification?.id ==
                                                        notification?.id
                                                            ? false
                                                            : !notification.read_at
                                                    "
                                                    class="w-2 h-2 bg-lime-500 rounded-full mt-2 flex-shrink-0"
                                                ></span>
                                            </div>
                                        </li>
                                    </ul>

                                    <!-- Read Notifications (when showAll is true) -->
                                    <ul
                                        v-if="
                                            showAllNotifications &&
                                            readNotifications.length > 0
                                        "
                                        class="space-y-2"
                                    >
                                        <li
                                            v-for="notification in readNotifications"
                                            :key="notification.id"
                                            class="p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition bg-gray-50"
                                            @click="
                                                showNotificationDetails(
                                                    notification,
                                                )
                                            "
                                        >
                                            <div class="flex items-start gap-3">
                                                <span
                                                    class="w-2 h-2 bg-gray-400 rounded-full mt-2 flex-shrink-0"
                                                ></span>
                                                <div class="flex-1">
                                                    <p
                                                        class="text-gray-600 text-sm"
                                                    >
                                                        {{
                                                            notification.data
                                                                .message
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-400 mt-1"
                                                    >
                                                        {{
                                                            formatTime(
                                                                notification.created_at,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <button
                                                    @click.stop="
                                                        markAsUnread(
                                                            notification.id,
                                                        )
                                                    "
                                                    class="text-xs text-gray-400 hover:text-gray-600 ml-2"
                                                    title="Mark as unread"
                                                >
                                                    <i
                                                        class="fa-solid fa-envelope"
                                                    ></i>
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </Popper>
                </div>

                <!-- Cart Dropdown -->
                <div class="relative flex justify-center">
                    <Popper
                        v-model:visible="isCartOpen"
                        :offset-distance="'0'"
                        placement="bottom"
                    >
                        <!-- Cart Icon with Count -->
                        <div
                            class="relative flex justify-center items-center text-2xl cursor-pointer"
                            @click="isCartOpen = !isCartOpen"
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
                                                                2,
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
                                                @click="enrollCourse"
                                                :disabled="isLoading"
                                                class="mt-4 w-full bg-lime-600 hover:bg-lime-700 text-white py-2 px-4 rounded-lg font-semibold transition flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed"
                                            >
                                                <template v-if="!isLoading">
                                                    Proceed to Checkout
                                                </template>
                                                <template v-else>
                                                    <svg
                                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <circle
                                                            class="opacity-25"
                                                            cx="12"
                                                            cy="12"
                                                            r="10"
                                                            stroke="currentColor"
                                                            stroke-width="4"
                                                        ></circle>
                                                        <path
                                                            class="opacity-75"
                                                            fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                        ></path>
                                                    </svg>
                                                    Processing...
                                                </template>
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
                        @click="toggleDropdown"
                        title="Profile"
                        class="w-8 h-8 rounded-full shadow-lg cursor-pointer"
                    />
                    <div
                        v-else
                        ref="profileBtnRef"
                        @click="toggleDropdown"
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
                                        () => {
                                            changeTab();
                                            closeDropdown();
                                        }
                                    "
                                    class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 rounded flex items-center gap-2"
                                >
                                    <i class="fas fa-book text-gray-500"></i>
                                    My Courses
                                </button>
                            </li>
                        </ul>

                        <div
                            @click="
                                () => {
                                    openProfile();
                                    closeDropdown();
                                }
                            "
                            class="block px-4 py-2 hover:bg-gray-200 rounded cursor-pointer"
                            title="Account"
                        >
                            <i class="fa-solid fa-user text-gray-500"></i>

                            Account
                        </div>

                        <hr class="my-2 border-gray-300" />

                        <ul>
                            <li>
                                <button
                                    @click.prevent="
                                        () => {
                                            signOut();
                                            closeDropdown();
                                        }
                                    "
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

                <!-- Mobile Menu Toggle -->
                <button
                    @click="toggleMenu"
                    class="block md:hidden text-2xl text-white focus:outline-none"
                >
                    <i
                        :class="
                            isMenuOpen
                                ? 'fa-solid fa-xmark'
                                : 'fa-solid fa-bars'
                        "
                    ></i>
                </button>
            </div>
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
                            href="/"
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

    <!-- Notification Details Modal -->
    <div
        v-if="showNotificationModal && currentNotification"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeNotificationModal"
    >
        <div
            class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto"
        >
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between p-4 border-b border-gray-200"
            >
                <h3 class="text-lg font-semibold text-gray-800">
                    Notification Details
                </h3>
                <button
                    @click="closeNotificationModal"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4">
                <div class="mb-4">
                    <p class="text-gray-600">
                        {{ currentNotification.data.message }}
                    </p>
                </div>

                <div class="flex items-center text-sm text-gray-500 mb-6">
                    <i class="fa-regular fa-clock mr-2"></i>
                    <span>{{
                        formatTime(currentNotification.created_at)
                    }}</span>
                </div>

                <!-- Additional details based on notification type -->
                <div
                    v-if="currentNotification.data.additional_data"
                    class="bg-gray-50 p-3 rounded-lg mb-4"
                >
                    <h4 class="font-medium text-gray-700 mb-2">Details:</h4>
                    <pre class="text-sm text-gray-600 whitespace-pre-wrap">{{
                        JSON.stringify(
                            currentNotification.data.additional_data,
                            null,
                            2,
                        )
                    }}</pre>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="flex justify-end p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg"
            >
                <button
                    @click="closeNotificationModal"
                    class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<style>
.notification-item.unread {
    background-color: #f8f9fa;
}

.notification-dot {
    width: 8px;
    height: 8px;
    background-color: #28a745;
    border-radius: 50%;
    display: inline-block;
    margin-left: 5px;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
