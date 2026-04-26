<script setup>
import Axios from "axios";
import Popper from "vue3-popper";
import { storeToRefs } from "pinia";
import { onMounted, ref, watch, onBeforeUnmount, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useSidebarStore } from "@/store/useSidebarStore";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore"; 

const router = useRouter();
const route = useRoute();

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
    selectedComponentId,
    otpEmail,
} = storeToRefs(appStore);

// Student refs
const { landingPageTab, selectedCourseSlug, myCourseTab, freeCourses, blogTab } =
    storeToRefs(studentStore);

// Notification refs
const notificationOpen = ref(false);
const notificationsLoading = ref(false);
const showAllNotifications = ref(false);
const currentNotification = ref(null);
const showNotificationModal = ref(false);

// UI state refs
const isCartOpen = ref(false);
const isMenuOpen = ref(false);
const isMenuVisible = ref(true);
const activeNav = ref("courses");

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
    if (notifications.value?.length === 0) return;
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

async function navigationToggle(id) {
    activeNav.value = id;
    await router.push("/");
    selectedComponentId.value = id;
    isMenuOpen.value = false;
}

function openFreeCourses() {
    activeNav.value = "free-courses";
    router.push({
        name: "student",
        query: { tab: freeCourses.value },
    });
    isMenuOpen.value = false;
}

function openBlog() {
    activeNav.value = "blog";
    router.push({
        name: "student",
        query: { tab: blogTab.value },
    });
    isMenuOpen.value = false;
}

// Checkout flow
async function enrollCourse() {
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }

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
    });
}

function getCartItemName(item) {
    return item?.name || item?.course_name || item?.title || "Item";
}

function getCartItemImage(item) {
    return item?.image || item?.thumbnail_url || item?.cover_page_url || "/images/course-1.jpg";
}

function navButtonClass(tabKey) {
    return [
        "font-semibold transition-colors",
        activeNav.value === tabKey
            ? "text-lime-600"
            : "text-gray-700 hover:text-lime-500",
    ];
}

function mobileNavButtonClass(tabKey) {
    return [
        "w-full text-left px-4 py-3 rounded-lg font-semibold transition-colors",
        activeNav.value === tabKey
            ? "bg-lime-100 text-lime-700"
            : "text-gray-700 hover:bg-lime-50 hover:text-lime-600",
    ];
}

function syncActiveNavFromRoute() {
    const queryTab = route.query?.tab;

    if (route.name === "student") {
        if (queryTab === freeCourses.value) {
            activeNav.value = "free-courses";
            return;
        }

        if (queryTab === blogTab.value) {
            activeNav.value = "blog";
            return;
        }

        if (queryTab === myCourseTab.value) {
            activeNav.value = "courses";
            return;
        }
    }

    if (selectedComponentId.value) {
        activeNav.value = selectedComponentId.value;
    }
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

async function signOut() {
    otpEmail.value = "";
    try {
        await Axios.post("/api/log-out");
        authUser.value = null;
        appStore.setAuthToken("");
        isLoggedIn.value = false;
        closeDropdown();
    } catch (error) {
        console.error("Logout failed:", error);
    }
}

function toggleAuthActions(actionType) {
    isMenuOpen.value = false;
    if (actionType === actionTypeLogin.value) {
        showLoginForm.value = true;
        showRegistrationForm.value = false;
    } else {
        showLoginForm.value = false;
        showRegistrationForm.value = true;
    }
}

// Toggle chat visibility
const toggleChat = () => {
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }
};

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
            `App.Models.User.${authUser.value.id}`
        ).notification(() => {
            appStore.fetchUnreadNotifications();
            if (showAllNotifications.value) {
                appStore.fetchReadNotifications();
            }
        });
    }

    // Initialize chat store
    if (authUser.value?.id) {
    }
});

const refreshNOtification = () => {
    notificationsLoading.value = true;
    appStore.fetchUnreadNotifications();

    notificationsLoading.value = false;
    return;
};

const refreshNotification = refreshNOtification;

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

watch(
    () => [route.name, route.query?.tab, selectedComponentId.value],
    syncActiveNavFromRoute,
    { immediate: true }
);

watch(
    items,
    (newItems) => {
        localStorage.setItem("cartItems", JSON.stringify(newItems));
    },
    { deep: true }
);
</script>

<template>
    <header class="fixed top-0 left-0 w-full z-50 bg-white shadow-sm transition-transform duration-300 ease-in-out"
        :class="{
            'translate-y-0': isMenuVisible,
            '-translate-y-full': !isMenuVisible,
        }">
        <div class=" mx-auto px-4 sm:px-6 lg:px-12">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <router-link to="/" class="flex items-center gap-3">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-xl "></div>
                        <img src="/images/logo.jpg" alt="Logo" class="relative h-16 w-20 rounded-xl object-contain" />
                    </div>
                    <div class="hidden lg:flex flex-col">
                        <span class="text-xl font-black text-gray-900">Unique<span
                                class="text-lime-500">English</span></span>
                    </div>
                </router-link>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <button @click="navigationToggle('courses')" :class="navButtonClass('courses')">
                        Courses
                    </button>
                    <button @click="openFreeCourses" :class="navButtonClass('free-courses')">
                        Free Courses
                    </button>
                    
                    <button @click="navigationToggle('books')" :class="navButtonClass('books')">
                        Books
                    </button>
                    <button @click="navigationToggle('live')" :class="navButtonClass('live')">
                        Live Sessions
                    </button>
                    <button @click="openBlog" :class="navButtonClass('blog')">
                        Blog
                    </button>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <!-- Notification Button -->
                    <div v-if="isLoggedIn" class="relative flex justify-center">
                        <Popper v-model:visible="notificationOpen" :offset-distance="'0'" placement="bottom">
                            <!-- Notification Icon with Count -->
                            <div class="relative flex justify-center items-center cursor-pointer p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                @click="toggleNotifications">
                                <div class="relative">
                                    <i class="fa-solid fa-bell text-xl text-gray-700"></i>
                                    <span v-if="unreadNotifications > 0"
                                        class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center text-xs font-bold text-white bg-lime-500 rounded-full border-2 border-white shadow">
                                        {{ unreadNotifications }}
                                    </span>
                                </div>
                            </div>

                            <!-- Dropdown -->
                            <template #content>
                                <div
                                    class="z-50 w-screen max-w-screen px-4 sm:px-0 sm:w-[450px] sm:max-w-lg sm:shadow-2xl mt-4">
                                    <div
                                        class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 max-h-[80vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300">
                                        <!-- Header -->
                                        <div class="border-b pb-3 mb-3 flex justify-between items-center">
                                            <h3 class="text-xl font-semibold text-gray-700">
                                                Notifications
                                            </h3>
                                            <div class="flex flex-row gap-4">
                                                <button @click="refreshNotification"
                                                    class="text-gray-500 hover:text-gray-800"
                                                    :disabled="notificationsLoading">
                                                    <i class="fas fa-sync-alt transition-transform" :class="{
                                                        'animate-spin':
                                                            notificationsLoading,
                                                    }" />
                                                </button>

                                                <button @click="
                                                    toggleShowAllNotifications
                                                " class="text-xs text-lime-500 hover:text-lime-600">
                                                    {{
                                                        showAllNotifications
                                                            ? "Show Unread Only"
                                                            : "Show All"
                                                    }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- No notifications -->
                                        <div v-if="
                                            notifications.length === 0 &&
                                            (!showAllNotifications ||
                                                readNotifications?.length === 0)
                                        "
                                            class="p-4 text-gray-500 text-sm flex flex-col items-center justify-center h-40">
                                            <i class="fa-regular fa-bell-slash text-4xl text-gray-400 mb-3"></i>
                                            <p>No new notifications</p>
                                        </div>

                                        <!-- Unread Notifications -->
                                        <ul v-if="notifications.length > 0" class="space-y-2">
                                            <li v-for="notification in notifications" :key="notification.id" :class="{
                                                hidden:
                                                    showAllNotifications &&
                                                    notification.data.read_at,
                                            }" class="p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition"
                                                @click="
                                                    showNotificationDetails(
                                                        notification
                                                    )
                                                    ">
                                                <div class="flex items-start gap-3">
                                                    <div>
                                                        <p class="font-medium text-sm">
                                                            {{
                                                                notification.data
                                                                    .message
                                                            }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            {{
                                                                formatTime(
                                                                    notification.created_at
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                    <span v-if="
                                                        currentNotification?.id ==
                                                            notification?.id
                                                            ? false
                                                            : !notification.read_at
                                                    "
                                                        class="w-2 h-2 bg-lime-400 rounded-full mt-2 flex-shrink-0"></span>
                                                </div>
                                            </li>
                                        </ul>

                                        <!-- Read Notifications (when showAll is true) -->
                                        <ul v-if="
                                            showAllNotifications &&
                                            readNotifications?.length > 0
                                        " class="space-y-2">
                                            <li v-for="notification in readNotifications" :key="notification.id"
                                                class="p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition bg-gray-50"
                                                @click="
                                                    showNotificationDetails(
                                                        notification
                                                    )
                                                    ">
                                                <div class="flex items-start gap-3">
                                                    <span
                                                        class="w-2 h-2 bg-gray-400 rounded-full mt-2 flex-shrink-0"></span>
                                                    <div class="flex-1">
                                                        <p class="text-gray-600 text-sm">
                                                            {{
                                                                notification.data
                                                                    .message
                                                            }}
                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            {{
                                                                formatTime(
                                                                    notification.created_at
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                    <button @click.stop="
                                                        markAsUnread(
                                                            notification.id
                                                        )
                                                        " class="text-xs text-gray-400 hover:text-gray-600 ml-2"
                                                        title="Mark as unread">
                                                        <i class="fa-solid fa-envelope"></i>
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
                        <Popper v-model:visible="isCartOpen" :offset-distance="'0'" placement="bottom">
                            <!-- Cart Icon with Count -->
                            <div class="relative flex justify-center items-center cursor-pointer p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                @click="isCartOpen = !isCartOpen">
                                <div class="relative">
                                    <i class="fa-solid fa-cart-shopping text-xl text-gray-700"></i>
                                    <span v-if="itemCount > 0"
                                        class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center text-xs font-bold text-white bg-lime-500 rounded-full border-2 border-white shadow">
                                        {{ itemCount }}
                                    </span>
                                </div>
                            </div>

                            <!-- Dropdown -->
                            <template #content>
                                <div
                                    class="z-50 w-screen max-w-screen px-4 sm:px-0 sm:w-[450px] sm:max-w-lg sm:shadow-2xl mt-4">
                                    <div
                                        class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 max-h-[80vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300">
                                        <!-- Header -->
                                        <div class="text-center border-b pb-3 mb-3">
                                            <h3 class="text-xl font-semibold text-gray-700">
                                                Your Cart
                                            </h3>
                                        </div>

                                        <!-- Empty Cart -->
                                        <div v-if="!itemCount"
                                            class="flex flex-col items-center justify-center h-40 space-y-3 text-gray-500">
                                            <i class="fa-solid fa-cart-shopping fa-fade text-4xl text-gray-400"></i>
                                            <h1 class="text-center text-base">
                                                No items in cart
                                            </h1>
                                            <button @click="navigationToggle('courses')"
                                                class="bg-lime-500 hover:bg-lime-600 text-white py-2 px-4 rounded-lg font-medium text-sm transition">
                                                <i class="fa-solid fa-book-open-reader mr-2"></i>Explore Courses
                                            </button>
                                        </div>

                                        <!-- Cart Items -->
                                        <div v-else>
                                            <ul class="space-y-3">
                                                <li v-for="item in items" :key="`${item.type}-${item.slug}`"
                                                    class="flex items-center justify-between gap-3 p-2 rounded hover:bg-gray-50 transition">
                                                    <img :src="getCartItemImage(item)" alt="Item Image"
                                                        class="w-14 h-14 rounded-lg object-cover border" />
                                                    <div class="flex-1">
                                                        <p class="font-medium text-sm truncate max-w-[20ch]">
                                                            {{ getCartItemName(item) }}
                                                        </p>
                                                        <p class="text-gray-500 text-sm">
                                                            ${{
                                                                Number(item.price || 0).toFixed(
                                                                    2
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                    <i @click="removeItem(item)"
                                                        class="fa-solid fa-minus text-red-500 hover:text-red-700 cursor-pointer"></i>
                                                </li>
                                            </ul>

                                            <!-- Footer -->
                                            <div class="mt-5 border-t pt-3">
                                                <div class="flex justify-between text-base font-semibold">
                                                    <span>Total:</span>
                                                    <span>${{
                                                        totalPrice.toFixed(2)
                                                        }}</span>
                                                </div>

                                                <button @click="enrollCourse" :disabled="isLoading"
                                                    class="mt-4 w-full bg-lime-500 hover:bg-lime-600 text-white py-2 px-4 rounded-lg font-semibold transition flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed">
                                                    <template v-if="!isLoading">
                                                        Proceed to Checkout
                                                    </template>
                                                    <template v-else>
                                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
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
                        <img v-if="authUser?.profile" :src="authUser.profile" alt="Profile" ref="profileBtnRef"
                            @click="toggleDropdown" title="Profile"
                            class="w-10 h-10 rounded-full shadow-sm cursor-pointer border-2 border-gray-200 hover:border-lime-500 transition-colors" />
                        <div v-else ref="profileBtnRef" @click="toggleDropdown"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-lime-400 to-lime-600 text-base font-bold text-white cursor-pointer shadow-sm hover:shadow-md transition-all">
                            {{ getInitials(authUser?.first_name) }}
                        </div>

                        <!-- Dropdown Menu -->
                        <div v-if="dropDownOpen" ref="dropdownRef"
                            class="absolute top-14 right-0 w-56 p-2 bg-white text-black rounded-xl shadow-xl border border-gray-100 z-50">
                            <ul>
                                <li>
                                    <button @click="
                                        () => {
                                            changeTab();
                                            closeDropdown();
                                        }
                                    "
                                        class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 rounded flex items-center gap-2">
                                        <i class="fas fa-book text-gray-500"></i>
                                        My Courses
                                    </button>
                                </li>
                            </ul>

                            <div @click="
                                () => {
                                    openProfile();
                                    closeDropdown();
                                }
                            " class="block px-4 py-2 hover:bg-gray-200 rounded cursor-pointer" title="Account">
                                <i class="fa-solid fa-user text-gray-500"></i>

                                Account
                            </div>

                            <hr class="my-2 border-gray-300" />

                            <ul>
                                <li>
                                    <button @click.prevent="
                                        () => {
                                            signOut();
                                            closeDropdown();
                                        }
                                    "
                                        class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 rounded flex items-center gap-2">
                                        <i class="fas fa-right-from-bracket"></i>
                                        Sign Out
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Login/Register -->
                    <div v-else class="hidden md:flex gap-3">
                        <button @click="toggleAuthActions(actionTypeLogin)"
                            class="px-5 py-2.5 font-semibold text-gray-700 rounded-lg border-2 border-gray-200 hover:border-lime-500 hover:text-lime-500 transition-all">
                            Sign In
                        </button>
                        <button @click="toggleAuthActions(actionTypeRegister)"
                            class="px-5 py-2.5 font-semibold text-white rounded-lg bg-gradient-to-r from-lime-500 to-lime-600 hover:shadow-lg hover:scale-105 transition-all">
                            Get Started
                        </button>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button @click="toggleMenu"
                        class="md:hidden text-2xl text-gray-700 focus:outline-none p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i :class="isMenuOpen
                                ? 'fa-solid fa-xmark'
                                : 'fa-solid fa-bars'
                            "></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <transition name="mobile-menu">
            <div v-if="isMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-lg">
                <div class="container mx-auto px-4 py-4">
                    <ul class="flex flex-col gap-2">
                        <li>
                            <button @click="navigationToggle('courses')" :class="mobileNavButtonClass('courses')">
                                Courses
                            </button>
                            <button  @click="openFreeCourses" :class="mobileNavButtonClass('free-courses')">
                                Free Courses
                            </button>
                        </li>

                        <li>
                            <button @click="navigationToggle('books')" :class="mobileNavButtonClass('books')">
                                Books
                            </button>
                        </li>
                        <li>
                            <button @click="navigationToggle('live')" :class="mobileNavButtonClass('live')">
                                Live Sessions
                            </button>
                        </li>
                        <li>
                            <button @click="openBlog" :class="mobileNavButtonClass('blog')">
                                Blog
                            </button>
                        </li>

                        <!-- Mobile Auth Buttons -->
                        <template v-if="!isLoggedIn">
                            <li class="pt-4 border-t border-gray-100 mt-2">
                                <button @click="toggleAuthActions(actionTypeLogin)"
                                    class="w-full px-4 py-3 font-semibold text-gray-700 rounded-lg border-2 border-gray-200 hover:border-lime-500 hover:text-lime-500 transition-all">
                                    Sign In
                                </button>
                            </li>
                            <li>
                                <button @click="toggleAuthActions(actionTypeRegister)"
                                    class="w-full px-4 py-3 font-semibold text-white rounded-lg bg-gradient-to-r from-lime-500 to-lime-600 hover:shadow-lg transition-all">
                                    Get Started
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </transition>
    </header>

    <!-- Notification Details Modal -->
    <div v-if="showNotificationModal && currentNotification"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeNotificationModal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    Notification Details
                </h3>
                <button @click="closeNotificationModal" class="text-gray-500 hover:text-gray-700">
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
                <div v-if="currentNotification.data.additional_data" class="bg-gray-50 p-3 rounded-lg mb-4">
                    <h4 class="font-medium text-gray-700 mb-2">Details:</h4>
                    <pre class="text-sm text-gray-600 whitespace-pre-wrap">{{
                        JSON.stringify(
                            currentNotification.data.additional_data,
                            null,
                            2
                        )
                    }}</pre>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button @click="closeNotificationModal"
                    class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition">
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
    background-color: #e58b3b;
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
