
<template>
    <header
      class="fixed top-0 left-0 w-full z-10 transition-transform duration-300 ease-in-out backdrop-blur-lg bg-lime-700/75 border border-white/20 shadow-lg rounded-b-2xl"
      :class="{
        'translate-y-0': isMenuVisible,
        '-translate-y-full': !isMenuVisible,
      }"
    >
      <nav class="container mx-auto flex items-center justify-between p-4">
        <a href="/" class="flex items-center gap-2">
          <img
            src="/images/logo.jpg"
            alt="Logo"
            class="h-12 w-12 rounded-full object-cover border-2 border-white"
          />
          <span class="text-xl font-bold text-white drop-shadow-md">UniqueEnglish</span>
        </a>

        <ul class="hidden md:flex items-center gap-6 text-lg font-medium">
          <li>
            <button @click="changeTab" class="nav-link">Home</button>
          </li>
          <li>
            <a href="#about" class="nav-link">About</a>
          </li>
          <li>
            <a href="#courses" class="nav-link">Courses</a>
          </li>
          <li>
            <a href="#contact" class="nav-link">Contact</a>
          </li>

          <!-- Cart Icon -->
          <div class="relative">
            <Popper placement="bottom-start">
              <div class="relative text-2xl flex items-center cursor-pointer">
                <i class="fa-solid fa-cart-plus text-white w-10 h-10"></i>
                <span
                  v-if="itemCount > 0"
                  class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >
                  {{ itemCount }}
                </span>
              </div>
              <template #content>
                <div class="cart-dropdown">
                  <h3 class="font-bold text-lg">Your Cart</h3>
                  <ul v-if="itemCount">
                    <li
                      v-for="item in items"
                      :key="item.id"
                      class="cart-item"
                    >
                      <img
                        :src="item.image"
                        alt="Item Image"
                        class="h-12 w-12 rounded object-cover"
                      />
                      <div class="ml-2">
                        <span class="block font-semibold">{{ item?.name }}</span>
                        <span class="text-gray-500 text-sm">${{ item?.price.toFixed(2) }}</span>
                      </div>
                      <i
                        @click="removeItem(item)"
                        class="fa-solid fa-minus text-red-500 hover:text-red-700 cursor-pointer"
                      ></i>
                    </li>
                  </ul>
                  <div class="mt-4 font-bold text-lg">
                    Total: ${{ totalPrice.toFixed(2) }}
                  </div>
                  <button
                    @click="enrollCourse()"
                    class="checkout-btn"
                  >
                    Proceed to Checkout
                  </button>
                </div>
              </template>
            </Popper>
          </div>

          <!-- Login & Register Call-to-Action Buttons -->
          <div class="flex gap-4">
            <router-link to="/login" class="btn-login">Login</router-link>
            <router-link to="/register" class="btn-register">Register</router-link>
          </div>
        </ul>

        <!-- Mobile Menu Toggle -->
        <button
          @click="toggleMenu"
          class="block md:hidden text-2xl text-white focus:outline-none"
        >
          <span v-if="!isMenuOpen">☰</span>
          <span v-else>✖</span>
        </button>
      </nav>

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

  <script setup>
  import Axios from "axios";
  import Popper from "vue3-popper";
  import { storeToRefs } from "pinia";
  import { useRouter } from "vue-router";
  import { ref } from "vue";
  import { useAppStore } from "@/store/useAppStore";
  import { useCartStore } from "@/store/useCartStore";
  import { UseStudentStore } from "@/store/UseStudentStore";

  const cartStore = useCartStore();
  const studentStore = UseStudentStore();
  const router = useRouter();

  const checkoutUrl = ref(null);
  const { items, itemCount, totalPrice } = storeToRefs(cartStore);
  const { landingPageTab, selectedCourseSlug } = storeToRefs(studentStore);
  const isMenuOpen = ref(false);
  const isMenuVisible = ref(true);

  const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
  };

  function enrollCourse() {
    Axios.post("/api/initiate-payment", { cartItems: items.value })
      .then((res) => {
        checkoutUrl.value = res.data.checkout_url;
        window.open(checkoutUrl.value, "_blank");
      })
      .catch((error) => {});
  }

  function removeItem(item) {
    let selectedItem = {
      type: "course",
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
  </script>

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

