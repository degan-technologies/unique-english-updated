<script setup>
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useSidebarStore } from '@/store/sidebar'

// Use defineEmits to let the parent know which event is emitted
const emit = defineEmits(['selectContent'])

// Access the sidebar state and toggle (for mobile slide-in/out)
const sidebarStore = useSidebarStore()
const { sideBarOpen, sidebarCollapsed } = storeToRefs(sidebarStore)
const toggleSidebar = sidebarStore.toggleSidebar

// Local state to control collapse/expand (affects desktop view)
const collapsed = ref(false)
function toggleCollapse() {
  collapsed.value = !collapsed.value
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// Define the sidebar menu items with Material Icon names and descriptions
const mainItems = [
  {
    label: 'Dashboard',
    route: 'dashboard',
    description: 'Overview of revenue, users, courses, and system health.',
    icon: 'dashboard'
  },
  {
    label: 'Users Management',
    route: 'users',
    description: 'Manage students, instructors, and admins.',
    icon: 'people'
  },
  {
    label: 'Course Management',
    route: 'courses',
    description: 'Manage courses and books.',
    icon: 'menu_book'
  },
  {
    label: 'Payments & Revenue',
    route: 'payments',
    description: 'Transactions, earnings, payouts.',
    icon: 'attach_money'
  },
  {
    label: 'Live Sessions',
    route: 'live-sessions',
    description: 'Track, join, schedule live classes.',
    icon: 'videocam'
  },
  {
    label: 'Messaging & Notifications',
    route: 'messaging',
    description: 'Send & manage messages.',
    icon: 'email'
  },
  {
    label: 'System Reports & Analytics',
    route: 'reports',
    description: 'View performance metrics.',
    icon: 'bar_chart'
  },
  {
    label: 'Settings & Customization',
    route: 'settings',
    description: 'Configure platform branding & security.',
    icon: 'settings'
  }
]

const courseItems = [
  {
    label: 'Add Course',
    route: 'addcourse',
    description: 'Approve, edit, delete courses.',
    icon: 'library_add'
  },
  {
    label: 'View Course',
    route: 'viewcourse',
    description: 'Manage course details.',
    icon: 'menu_book'
  }
]

const miscItems = [
  {
    label: 'Notices',
    route: 'notice',
    description: 'Send and view notices.',
    icon: 'notifications'
  },
  {
    label: 'Controls',
    route: 'controls',
    description: 'Manage system controls.',
    icon: 'tune'
  }
]

// When a menu item is clicked, emit the event so the parent can update the main content area.
function selectContent(route) {
  console.log('Selected route:', route)
  emit('selectContent', route)
}
</script>

<template>
  <!-- Sidebar container: slides in/out on mobile -->
  <aside
    :class="[
      'fixed top-0 left-0 h-screen z-50 transition-transform duration-300 ease-in-out',
      sideBarOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <!-- Sidebar panel: width changes when collapsed -->
    <div
      :class="[
        collapsed ? 'w-16' : 'w-64',
        'bg-gray-50 border-r border-lime-300 h-full relative flex flex-col transition-all duration-300'
      ]"
    >
      <!-- Header with logo and mobile close button (desktop: no close icon) -->
      <div class="flex items-center justify-between h-20 px-4 border-b border-lime-300">
        <!-- Logo: Full Logo (if not collapsed) -->
        <div v-if="!collapsed" class="flex items-center animate-fadeIn">
          <img 
            src="images/logo.jpg" 
            alt="Full Logo" 
            class="h-12 w-auto object-contain"
          />
          <span class="ml-2 font-semibold text-xl text-lime-500">UniqueEnglish</span>
        </div>
        
        <!-- Logo: Abbreviated Logo (if collapsed) -->
        <div v-else class="flex items-center animate-fadeIn">
          <img 
            src="images/logo.jpg" 
            alt="Logo Abbreviation" 
            class="h-10 w-auto object-contain"
          />
        </div>
        
        <!-- Mobile Close Icon (only visible on mobile) -->
        <button 
          @click="toggleSidebar()" 
          class="text-lime-500 hover:text-lime-600 md:hidden transition-colors"
          title="Close Sidebar"
        >
          <span class="material-icons animate-spinIn">close</span>
        </button>
      </div>

      <!-- Scrollable menu content -->
      <div class="flex-1 overflow-y-auto">
        <!-- MAIN Section -->
        <div class="px-4 py-2">
          <p v-if="!collapsed" class="text-sm font-semibold mb-2 text-lime-600 animate-fadeIn">MAIN</p>
          <ul>
            <li
              v-for="(item, index) in mainItems"
              :key="index"
              @click="selectContent(item.route)"
              class="flex items-center space-x-2 p-2 hover:bg-lime-100 rounded-lg cursor-pointer transition-all duration-200"
              :title="item.description"
            >
              <span class="material-icons text-lime-500 transition-colors duration-200">
                {{ item.icon }}
              </span>
              <span v-if="!collapsed" class="text-gray-800 animate-fadeIn">{{ item.label }}</span>
            </li>
          </ul>
        </div>

        <!-- COURSES Section -->
        <div class="px-4 py-2">
          <p v-if="!collapsed" class="text-sm font-semibold mb-2 text-lime-600 animate-fadeIn">COURSES</p>
          <ul>
            <li
              v-for="(item, index) in courseItems"
              :key="index"
              @click="selectContent(item.route)"
              class="flex items-center space-x-2 p-2 hover:bg-lime-100 rounded-lg cursor-pointer transition-all duration-200"
              :title="item.description"
            >
              <span class="material-icons text-lime-500 transition-colors duration-200">
                {{ item.icon }}
              </span>
              <span v-if="!collapsed" class="text-gray-800 animate-fadeIn">{{ item.label }}</span>
            </li>
          </ul>
        </div>

        <!-- MISC Section -->
        <div class="px-4 py-2">
          <p v-if="!collapsed" class="text-sm font-semibold mb-2 text-lime-600 animate-fadeIn">MISC</p>
          <ul>
            <li
              v-for="(item, index) in miscItems"
              :key="index"
              @click="selectContent(item.route)"
              class="flex items-center space-x-2 p-2 hover:bg-lime-100 rounded-lg cursor-pointer transition-all duration-200"
              :title="item.description"
            >
              <span class="material-icons text-lime-500 transition-colors duration-200">
                {{ item.icon }}
              </span>
              <span v-if="!collapsed" class="text-gray-800 animate-fadeIn">{{ item.label }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Collapse/Expand Button at bottom right -->
      <button
        @click="toggleCollapse"
        class="absolute bottom-2 right-2 p-2 rounded-full bg-lime-300 hover:bg-lime-200 transition-colors duration-200"
      >
        <span
          class="material-icons text-lime-900 transform transition-transform duration-300"
          :class="collapsed ? 'rotate-0' : 'rotate-180'"
        >
          chevron_left
        </span>
      </button>
    </div>
  </aside>
</template>



<!-- Import Material Icons font and add custom animations -->
<style>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes spinIn {
  from {
    transform: rotate(-90deg);
    opacity: 0;
  }
  to {
    transform: rotate(0deg);
    opacity: 1;
  }
}
.animate-spinIn {
  animation: spinIn 0.5s ease-in-out;
}
</style>
