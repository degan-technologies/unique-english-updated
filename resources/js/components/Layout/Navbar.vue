<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSidebarStore } from '@/store/sidebar'
import { useThemeStore } from '@/store/theme'

const sidebarStore = useSidebarStore()
const { sideBarOpen } = storeToRefs(sidebarStore)
const dropDownOpen = ref(false)
const mobileMenuOpen = ref(false)
const searchOpen = ref(false)

const themeStore = useThemeStore()

// Toggle Sidebar (updates sideBarOpen)
const toggleSidebar = () => {
  sidebarStore.toggleSidebar()
}

// Screen Resize Handling (if needed for your sidebar)
const updateScreenSize = () => {
  sidebarStore.checkScreenSize()
}

onMounted(() => {
  window.addEventListener('resize', updateScreenSize)
  updateScreenSize()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScreenSize)
})
</script>

<template>
  <div class="sticky top-0 z-40">
    <div
      class="w-full h-16 px-6 bg-gray-100  border-b flex items-center justify-between transition-colors duration-300"
    >
      <!-- Left Navbar -->
      <div class="flex items-center">
        <!-- Sidebar Toggle (visible on lg and below) -->
        <button
          class="lg:hidden text-gray-600 hover:text-lime-500 transition-colors duration-300"
          @click="toggleSidebar"
          title="Toggle Sidebar"
        >
          <span class="material-icons">menu</span>
        </button>

        <!-- Search Bar -->
        <div class="relative ml-4">
          <!-- Desktop/Tablet: Always show search input -->
          <div class="hidden md:block">
            <input
              type="text"
              placeholder="Search..."
              class="bg-white  h-10 w-64 px-5 rounded-lg border text-sm text-gray-700  focus:outline-none transition-colors duration-300"
            />
            <button
              type="submit"
              class="absolute right-0 top-0 mt-2 mr-4 text-gray-600  hover:text-lime-500 transition-colors duration-300"
              title="Search"
            >
              <span class="material-icons">search</span>
            </button>
          </div>
          <!-- Mobile: Show search icon; when clicked, display inline search input -->
          <div class="block md:hidden">
            <template v-if="searchOpen">
              <div class="flex items-center">
                <button
                  @click="searchOpen = false"
                  class="mr-2 text-gray-600 hover:text-lime-500 transition-colors duration-300"
                  title="Back"
                >
                  <span class="material-icons">arrow_back</span>
                </button>
                <input
                  type="text"
                  placeholder="Search..."
                  class="flex-1 h-10 px-4 rounded-lg border text-sm text-gray-700  bg-gray  focus:outline-none transition-colors duration-300"
                />
              </div>
            </template>
            <template v-else>
              <button
                @click="searchOpen = true"
                class="text-gray-600  hover:text-lime-500 transition-colors duration-300"
                title="Search"
              >
                <span class="material-icons">search</span>
              </button>
            </template>
          </div>
        </div>
      </div>

      <!-- Right Navbar -->
      <div class="flex items-center space-x-4 text-gray-600 ">
        <!-- Theme Toggle (always visible and kept outside the mobile dropdown) -->
        <button
          @click="themeStore.toggleTheme"
          class="hover:text-lime-500 transition-colors duration-300"
          title="Toggle Dark Mode"
        >
          <span class="material-icons">
            {{ themeStore.theme === 'dark' ? 'light_mode' : 'dark_mode' }}
          </span>
        </button>

        <!-- Desktop/Tablet: Show all right nav items -->
        <div class="hidden md:flex items-center space-x-4">
          <!-- Notifications -->
          <button
            class="hover:text-lime-500 relative transition-colors duration-300"
            title="Notifications"
          >
            <span class="material-icons">notifications</span>
            <span
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"
            >
              3
            </span>
          </button>

          <!-- Quick Messaging -->
          <button
            class="hover:text-lime-500 transition-colors duration-300"
            title="Quick Messaging"
          >
            <span class="material-icons">chat</span>
          </button>

          <!-- Role Switcher -->
          <button
            class="hover:text-lime-500 transition-colors duration-300"
            title="Switch Role (Admin ↔ Instructor)"
          >
            <span class="material-icons">swap_horiz</span>
          </button>

          <!-- Settings -->
          <button
            class="hover:text-lime-500 transition-colors duration-300"
            title="Settings"
          >
            <span class="material-icons">settings</span>
          </button>

          <!-- Profile Picture with Dropdown -->
          <div class="relative">
            <img
              src="https://a7sas.net/wp-content/uploads/2019/07/4060.jpeg"
              class="w-12 h-12 rounded-full shadow-lg cursor-pointer"
              @click="dropDownOpen = !dropDownOpen"
              title="Profile"
            />
            <!-- Profile Dropdown Menu -->
            <div
              v-if="dropDownOpen"
              class="absolute top-14 right-0 bg-white  border border-gray-200  shadow-xl text-gray-700  rounded-lg w-48 transition-all duration-300"
            >
              <a
                href="#"
                class="block px-4 py-2 hover:bg-gray-200 "
                title="Account"
              >
                Account
              </a>
              <a
                href="#"
                class="block px-4 py-2 hover:bg-gray-200 "
                title="Settings"
              >
                Settings
              </a>
              <a
                href="#"
                class="block px-4 py-2 hover:bg-gray-200 "
                title="Logout"
              >
                Logout
              </a>
            </div>
          </div>
        </div>

        <!-- Mobile: Three Dot Menu for right nav items (except theme toggle) -->
        <div class="relative md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="hover:text-lime-500 transition-colors duration-300"
            title="More Options"
          >
            <span class="material-icons">more_vert</span>
          </button>
          <div
            v-if="mobileMenuOpen"
            class="absolute right-0 mt-2 bg-white  border border-gray-200  shadow-xl text-gray-700  rounded-lg w-48 transition-all duration-300"
          >
            <a
              href="#"
              class="block px-4 py-2 hover:bg-gray-200 "
              title="Notifications"
            >
              <span class="material-icons align-middle">notifications</span>
              <span class="ml-2">Notifications</span>
            </a>
            <a
              href="#"
              class="block px-4 py-2 hover:bg-gray-200 "
              title="Quick Messaging"
            >
              <span class="material-icons align-middle">chat</span>
              <span class="ml-2">Messaging</span>
            </a>
            <a
              href="#"
              class="block px-4 py-2 hover:bg-gray-200 "
              title="Switch Role"
            >
              <span class="material-icons align-middle">swap_horiz</span>
              <span class="ml-2">Switch Role</span>
            </a>
            <a
              href="#"
              class="block px-4 py-2 hover:bg-gray-200 "
              title="Settings"
            >
              <span class="material-icons align-middle">settings</span>
              <span class="ml-2">Settings</span>
            </a>
            <a
              href="#"
              class="block px-4 py-2 hover:bg-gray-200 "
              title="Profile"
            >
              <span class="material-icons align-middle">account_circle</span>
              <span class="ml-2">Profile</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.material-icons {
  font-size: 24px;
  transition: color 0.3s;
}
</style>
